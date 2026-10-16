<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flower;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ribbon;
use App\Models\WrappingPaper;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Every stock-tracked model, with what the Low Stock Alerts widget
     * needs to query and link to each one generically: its display label,
     * the attribute customers see it by, and its admin edit route name.
     */
    protected const STOCK_TRACKED = [
        [Product::class, 'Product', 'title', 'admin.products.edit'],
        [Flower::class, 'Flower', 'name', 'admin.flowers.edit'],
        [WrappingPaper::class, 'Wrapping Paper', 'name', 'admin.wrapping-papers.edit'],
        [Ribbon::class, 'Ribbon', 'name', 'admin.ribbons.edit'],
    ];

    public function index(): View
    {
        $todayOrders = Order::query()->today()->count();
        $pendingOrders = Order::query()->where('status', 'pending')->count();

        $dailyRevenue = (float) Order::query()
            ->today()
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $monthlyRevenue = (float) Order::query()
            ->thisMonth()
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $threshold = config('shop.low_stock_threshold');
        $lowStockItems = collect();
        $outOfStockCount = 0;

        foreach (self::STOCK_TRACKED as [$model, $type, $labelAttribute, $editRoute]) {
            $lowStockItems = $lowStockItems->merge(
                $model::query()
                    ->where('is_active', true)
                    ->where('stock_quantity', '>', 0)
                    ->where('stock_quantity', '<=', $threshold)
                    ->get()
                    ->map(fn ($item) => (object) [
                        'type' => $type,
                        'name' => $item->{$labelAttribute},
                        'stock' => $item->stock_quantity,
                        'editUrl' => route($editRoute, $item),
                    ])
            );

            $outOfStockCount += $model::query()->where('is_active', true)->where('stock_quantity', '<=', 0)->count();
        }

        $lowStockItems = $lowStockItems->sortBy('stock')->values();

        $recentOrders = Order::query()->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'todayOrders' => $todayOrders,
            'pendingOrders' => $pendingOrders,
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'lowStockItems' => $lowStockItems,
            'outOfStockCount' => $outOfStockCount,
            'recentOrders' => $recentOrders,
            'totalProducts' => Product::query()->count(),
        ]);
    }
}
