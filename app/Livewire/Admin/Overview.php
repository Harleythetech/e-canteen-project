<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Admin Overview')]
class Overview extends Component
{
    public function render()
    {
        $stats = [
            'total_revenue' => Order::whereNotNull('paid_at')->sum('total'),
            'today_revenue' => Order::whereDate('created_at', today())->whereNotNull('paid_at')->sum('total'),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'active_orders' => Order::active()->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_products' => Product::count(),
            'low_stock' => Product::where('stock', '<=', 5)->where('is_available', true)->count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('is_available', true)
            ->orderBy('stock')
            ->take(5)
            ->get();

        // 7-day revenue trend
        $revenueTrend = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->whereNotNull('paid_at')
            ->selectRaw("DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trendLabels = [];
        $trendRevenue = [];
        $trendOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trendLabels[] = now()->subDays($i)->format('M d');
            $trendRevenue[] = (float) ($revenueTrend[$date]->revenue ?? 0);
            $trendOrders[] = (int) ($revenueTrend[$date]->orders ?? 0);
        }

        // Order status breakdown (all time)
        $statusBreakdown = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Category sales distribution
        $categorySales = \App\Models\OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotNull('orders.paid_at')
            ->selectRaw('categories.name as category, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        return view('livewire.admin.overview', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'trendLabels' => $trendLabels,
            'trendRevenue' => $trendRevenue,
            'trendOrders' => $trendOrders,
            'statusBreakdown' => $statusBreakdown,
            'categorySales' => $categorySales,
        ]);
    }
}
