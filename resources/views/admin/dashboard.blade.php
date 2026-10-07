<x-app-layout title="Dashboard">

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Today's Orders" icon="box">{{ $todayOrders }}</x-stat-card>
        <x-stat-card label="Pending Orders" icon="mail">{{ $pendingOrders }}</x-stat-card>
        <x-stat-card label="Today's Revenue" icon="cart"><x-price :amount="$dailyRevenue" /></x-stat-card>
        <x-stat-card label="This Month's Revenue" icon="cart"><x-price :amount="$monthlyRevenue" /></x-stat-card>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-3">

        {{-- Recent orders --}}
        <div class="card p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-xl text-espresso">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-mocha hover:underline">View all &rarr;</a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-blush text-xs uppercase tracking-wide text-mocha">
                            <th class="py-2 pr-4">Order</th>
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blush">
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="py-3 pr-4 font-semibold text-espresso">{{ $order->order_number }}</td>
                                <td class="py-3 pr-4 text-espresso/80">{{ $order->name }}</td>
                                <td class="py-3 pr-4"><x-order-status-badge :status="$order->status" /></td>
                                <td class="py-3 pr-4"><x-price :amount="$order->total" /></td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-mocha hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-espresso/60">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low stock alerts --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-xl text-espresso">Low Stock Alerts</h2>
                <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-mocha hover:underline">Manage &rarr;</a>
            </div>

            @if ($outOfStockCount > 0)
                <p class="mt-3 rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700">
                    {{ $outOfStockCount }} {{ Str::plural('product', $outOfStockCount) }} out of stock
                </p>
            @endif

            <ul class="mt-4 space-y-3">
                @forelse ($lowStockProducts as $product)
                    <li class="flex items-center justify-between text-sm">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-espresso hover:text-mocha">{{ $product->title }}</a>
                        <span class="badge bg-mocha/20 text-mocha">{{ $product->stock_quantity }} left</span>
                    </li>
                @empty
                    <li class="text-sm text-espresso/60">All stock levels look healthy.</li>
                @endforelse
            </ul>
        </div>
    </div>

</x-app-layout>
