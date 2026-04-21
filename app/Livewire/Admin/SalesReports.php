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
    #[Url]
    public string $period = 'today';

    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

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

    public function render()
    {
        [$from, $to] = $this->getDateRange();

        $ordersQuery = Order::whereBetween('created_at', [$from, $to]);

        $totalRevenue = (clone $ordersQuery)->whereNotNull('paid_at')->sum('total');
        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'completed')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / max((clone $ordersQuery)->whereNotNull('paid_at')->count(), 1) : 0;

        // Daily breakdown for the period
        $dailySales = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('paid_at')
            ->selectRaw("DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Top selling products in period
        $topProducts = OrderItem::whereHas('order', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to])->whereNotNull('paid_at');
        })
            ->selectRaw('product_name, SUM(quantity) as total_sold, SUM(quantity * unit_price) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Orders by status
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
