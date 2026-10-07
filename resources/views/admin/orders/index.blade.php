<x-app-layout title="Orders">

    <form method="GET" class="card mb-6 grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Order #, name, or phone" class="input lg:col-span-2">

        <select name="status" class="input">
            <option value="">All Statuses</option>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="payment_method" class="input">
            <option value="">All Payment Methods</option>
            @foreach ($paymentMethods as $key => $label)
                <option value="{{ $key }}" @selected(request('payment_method') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ request('from') }}" class="input">
        <input type="date" name="to" value="{{ request('to') }}" class="input">

        <div class="flex gap-2 lg:col-span-6">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn-outline">Clear</a>
        </div>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                    <th class="px-5 py-3">Order</th>
                    <th class="px-5 py-3">Customer</th>
                    <th class="px-5 py-3">Delivery</th>
                    <th class="px-5 py-3">Payment</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Total</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blush">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-semibold text-espresso">{{ $order->order_number }}</p>
                            <p class="text-xs text-espresso/60">{{ $order->created_at->format('d M Y, g:ia') }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-espresso">{{ $order->name }}</p>
                            <p class="text-xs text-espresso/60">{{ $order->phone }}</p>
                        </td>
                        <td class="px-5 py-3 text-espresso/80">
                            {{ $order->delivery_date->format('d M Y') }}<br>
                            <span class="text-xs text-espresso/60">{{ $order->delivery_time_slot }}</span>
                        </td>
                        <td class="px-5 py-3 text-espresso/80">{{ $order->paymentMethodLabel() }}</td>
                        <td class="px-5 py-3"><x-order-status-badge :status="$order->status" /></td>
                        <td class="px-5 py-3"><x-price :amount="$order->total" /></td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-mocha hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-espresso/60">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>

</x-app-layout>
