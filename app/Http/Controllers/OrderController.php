<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function confirmation(Order $order): View
    {
        return view('orders.confirmation', [
            'order' => $order->load('items', 'charges'),
        ]);
    }

    public function trackShow(): View
    {
        return view('orders.track');
    }

    /**
     * Looks an order up by order number + phone (rather than requiring a
     * customer account, which this shop intentionally doesn't have).
     * Phone digits are compared loosely so "960 777-1234" and
     * "9607771234" both match what the customer typed at checkout.
     */
    public function trackResult(TrackOrderRequest $request): View
    {
        $digitsOnly = preg_replace('/\D+/', '', $request->validated('phone'));

        $order = Order::query()
            ->where('order_number', trim($request->validated('order_number')))
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", ["%{$digitsOnly}%"])
            ->with('items', 'charges')
            ->first();

        return view('orders.track', [
            'order' => $order,
            'searched' => true,
        ]);
    }
}
