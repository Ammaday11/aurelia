<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
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

        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', config('shop.low_stock_threshold'))
            ->orderBy('stock_quantity')
            ->get();

        $outOfStockCount = Product::query()->where('is_active', true)->where('stock_quantity', '<=', 0)->count();

        $recentOrders = Order::query()->latest()->limit(8)->get();

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'todayOrders' => $todayOrders,
            'pendingOrders' => $pendingOrders,
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockCount' => $outOfStockCount,
            'recentOrders' => $recentOrders,
            'totalProducts' => Product::query()->count(),
        ]);
    }
}
