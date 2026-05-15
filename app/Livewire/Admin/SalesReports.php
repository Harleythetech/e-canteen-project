<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::admin')]
class SalesReports extends Component
{
    // Synced to the URL — controls the active period: 'today', 'week', 'month', or 'custom'
    #[Url]
    public string $period = 'today';

    // Used only when period = 'custom' — bound to the date range picker inputs
    public string $dateFrom = '';
    public string $dateTo = '';

    /**
     * Sets the default custom date range to the current month when the page loads.
     */
    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    /**
     * Returns the start and end Carbon timestamps for the selected period.
     * Used to scope all queries on this page to the same date range.
     *
     * - today: midnight to now
     * - week: Monday to Sunday of the current week
     * - month: 1st to last day of the current month
     * - custom: whatever the admin selected in the date pickers
     */
    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    /**
     * Loads all sales data for the selected period.
     *
     * Provides:
     * - totalRevenue: sum of all paid orders in the period
     * - totalOrders: count of all orders (paid or not) in the period
     * - completedOrders: orders that were picked up
     * - cancelledOrders: orders that were cancelled
     * - averageOrderValue: revenue divided by number of paid orders
     * - dailySales: day-by-day breakdown for the line chart
     * - topProducts: top 10 best-selling items by quantity in the period
     * - statusBreakdown: order counts per status for the doughnut chart
     */
    public function render()
    {
        [$from, $to] = $this->getDateRange();

        // Base query scoped to the selected date range
        $ordersQuery = Order::whereBetween('created_at', [$from, $to]);

        // Summary stat cards
        $totalRevenue = (clone $ordersQuery)->whereNotNull('paid_at')->sum('total');
        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'completed')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        // Avoid division by zero — use max(paidCount, 1) as the denominator
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / max((clone $ordersQuery)->whereNotNull('paid_at')->count(), 1) : 0;

        // Day-by-day revenue and order count — used by the line chart
        $dailySales = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('paid_at')
            ->selectRaw("DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Top 10 products by quantity sold in the period
        $topProducts = OrderItem::whereHas('order', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to])->whereNotNull('paid_at');
        })
            ->selectRaw('product_name, SUM(quantity) as total_sold, SUM(quantity * unit_price) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Order count per status in the period — used by the status doughnut chart
        $statusBreakdown = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('livewire.admin.sales-reports', [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'averageOrderValue' => $averageOrderValue,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'statusBreakdown' => $statusBreakdown,
        ]);
    }
}
