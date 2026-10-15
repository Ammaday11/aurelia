<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\CustomCharge;
use App\Models\Order;
use App\Models\OrderCharge;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->status($request->string('status')->toString() ?: null)
            ->paymentMethod($request->string('payment_method')->toString() ?: null)
            ->betweenDates($request->input('from'), $request->input('to'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'title' => 'Orders',
            'orders' => $orders,
            'statuses' => config('shop.statuses'),
            'paymentMethods' => config('shop.payment_methods'),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('items.product', 'charges');

        return view('admin.orders.show', [
            'title' => "Order {$order->order_number}",
            'order' => $order,
            'statuses' => Order::STATUSES,
            // Only "manual" charges are offered here — an "automatic" one
            // already applies itself to every order at checkout (see
            // OrderService::placeOrder()), so attaching it again here
            // would double it up.
            'availableCharges' => CustomCharge::query()->active()->manual()->ordered()->get(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderService $orders): RedirectResponse
    {
        $orders->updateStatus($order, $request->validated('status'));

        return back()->with('success', "Order #{$order->order_number} status updated to \"{$order->fresh()->statusLabel()}\".");
    }

    public function attachCharge(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'custom_charge_id' => ['required', 'integer', 'exists:custom_charges,id'],
        ]);

        $charge = CustomCharge::query()->active()->manual()->find($validated['custom_charge_id']);

        if (! $charge) {
            return back()->with('error', 'That charge is no longer available to add.');
        }

        $orders->attachCharge($order, $charge);

        return back()->with('success', "\"{$charge->name}\" was added to this order.");
    }

    public function detachCharge(Order $order, OrderCharge $orderCharge, OrderService $orders): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $orders->detachCharge($order, $orderCharge);

        return back()->with('success', "\"{$orderCharge->name}\" was removed from this order.");
    }

    public function invoice(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('items', 'charges');

        return view('admin.orders.invoice', [
            'title' => "Invoice {$order->order_number}",
            'order' => $order,
        ]);
    }
}
