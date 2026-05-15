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
    /**
     * Loads all data needed to render the admin dashboard overview.
     *
     * Provides:
     * - stats: key numbers for the summary cards at the top
     * - recentOrders: the 10 most recent orders for the activity feed
     * - lowStockProducts: items with 5 or fewer units for the stock alert section
     * - trendLabels/Revenue/Orders: 7-day data arrays for the revenue trend line chart
     * - statusBreakdown: all-time order counts per status for the doughnut chart
     * - categorySales: revenue per category for the category breakdown chart
     */
    public function render()
    {
        // Summary stat cards
        $stats = [
            'total_revenue' => Order::whereNotNull('paid_at')->sum('total'),
            'today_revenue' => Order::whereDate('created_at', today())->whereNotNull('paid_at')->sum('total'),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'active_orders' => Order::active()->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_products' => Product::count(),
            'low_stock' => Product::where('stock', '<=', 5)->count(),
        ];

        // Recent activity feed — latest 10 orders with the student's name
        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Products that need restocking
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->get();

        // 7-day revenue trend — builds arrays for Chart.js line chart
        $revenueTrend = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->whereNotNull('paid_at')
            ->selectRaw("DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date'); // Key by date string so we can look up each day below

        $trendLabels = [];
        $trendRevenue = [];
        $trendOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trendLabels[] = now()->subDays($i)->format('M d'); // e.g. "May 15"
            $trendRevenue[] = (float) ($revenueTrend[$date]->revenue ?? 0);
            $trendOrders[] = (int) ($revenueTrend[$date]->orders ?? 0);
        }

        // All-time order count per status — used by the status breakdown doughnut chart
        $statusBreakdown = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Revenue per category — used by the category sales bar/pie chart
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
