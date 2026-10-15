<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\ItemUnavailableException;
use App\Facades\Cart;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmation;
use App\Models\CustomCharge;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Cart::isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty — add something lovely first.');
        }

        // Shown to the customer before they place the order, so there are
        // no surprise fees — the same active+automatic charges OrderService
        // applies at checkout time (see OrderService::placeOrder()).
        $automaticCharges = CustomCharge::query()->active()->automatic()->ordered()->get();

        return view('checkout.index', [
            'items' => Cart::content(),
            'subtotal' => Cart::subtotal(),
            'deliveryFee' => (float) Setting::get('delivery_fee', config('shop.default_delivery_fee')),
            'automaticCharges' => $automaticCharges,
            'timeSlots' => Setting::getJson('delivery_time_slots', config('shop.default_delivery_time_slots')),
            'minDeliveryDate' => now()->addDay()->toDateString(),
            'bankDetails' => [
                'bank_name' => Setting::get('bank_name'),
                'account_name' => Setting::get('bank_account_name'),
                'account_number' => Setting::get('bank_account_number'),
            ],
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders): RedirectResponse
    {
        try {
            $order = $orders->placeOrder(
                $request->validated(),
                $request->file('payment_receipt'),
                $request->ip(),
            );
        } catch (InsufficientStockException|ItemUnavailableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($order->email) {
            try {
                Mail::to($order->email)->send(new OrderConfirmation($order));
            } catch (\Throwable $e) {
                // The order is already safely placed; a transport hiccup
                // shouldn't block the customer from seeing their confirmation.
                Log::warning("Failed to send order confirmation email for {$order->order_number}: ".$e->getMessage());
            }
        }

        return redirect()->route('orders.confirmation', $order->order_number);
    }
}
