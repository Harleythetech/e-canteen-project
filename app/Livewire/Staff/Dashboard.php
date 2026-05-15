<?php

namespace App\Livewire\Staff;

use App\Models\Order;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::staff')]
#[Title('Staff Dashboard')]
class Dashboard extends Component
{
    #[Url]
    public string $tab = 'orders';

    public string $statusFilter = 'active';
    public string $scannedCode = '';

    public function refreshOrders(): void
    {
        // Triggers a re-render without resetting any state.
    }

    public function advanceOrder(int $orderId): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('updateStatus', $order);

        $nextStatus = match ($order->status) {
            'pending' => 'paid',
            'paid' => 'preparing',
            'preparing' => 'ready',
            'ready' => 'completed',
            default => null,
        };

        if ($nextStatus && $order->canTransitionTo($nextStatus)) {
            $order->transitionTo($nextStatus);
            $label = ucfirst($nextStatus);
            $this->dispatch('toast', type: 'success', message: "Order {$order->order_number} marked as {$label}.");
        } else {
            $this->dispatch('toast', type: 'error', message: 'Could not advance order status.');
        }
    }

    public function processQrCode(): void
    {
        $code = trim($this->scannedCode);
        if (empty($code)) {
            return;
        }

        $order = Order::where('order_number', $code)->first();

        if (!$order) {
            $this->dispatch('scanner-result', type: 'error', message: 'Order not found.');
            $this->scannedCode = '';
            return;
        }

        if ($order->status === 'ready') {
            $order->transitionTo('completed');
            $this->dispatch('scanner-result', type: 'success', message: "Order {$order->order_number} completed!");
        } elseif ($order->status === 'completed') {
            $this->dispatch('scanner-result', type: 'info', message: "Order {$order->order_number} was already completed.");
        } else {
            $this->dispatch('scanner-result', type: 'error', message: "Order {$order->order_number} is not ready for pickup (status: {$order->status}).");
        }

        $this->scannedCode = '';
    }

    public function render()
    {
        $orders = Order::with(['items', 'user'])
            ->when($this->statusFilter === 'active', fn($q) => $q->active())
            ->when($this->statusFilter === 'pending', fn($q) => $q->pending())
            ->when($this->statusFilter === 'paid', fn($q) => $q->paid())
            ->when($this->statusFilter === 'preparing', fn($q) => $q->preparing())
            ->when($this->statusFilter === 'ready', fn($q) => $q->ready())
            ->latest()
            ->get();

        $todayStats = [
            'total_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::whereDate('created_at', today())->whereNotNull('paid_at')->sum('total'),
            'pending_orders' => Order::active()->count(),
            'completed_today' => Order::completed()->whereDate('completed_at', today())->count(),
        ];

        $topProducts = Product::withSum([
            'orderItems as today_sold' => function ($q) {
                $q->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereDate('orders.created_at', today())
                    ->whereNotNull('orders.paid_at');
            }
        ], 'quantity')
            ->orderByDesc('today_sold')
            ->take(10)
            ->get()
            ->filter(fn($p) => ($p->today_sold ?? 0) > 0);

        // Hourly sales for today (line chart)
        $hourlySales = Order::whereDate('created_at', today())
            ->whereNotNull('paid_at')
            ->selectRaw("EXTRACT(HOUR FROM created_at) as hour, SUM(total) as revenue, COUNT(*) as orders")
            ->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyLabels = [];
        $hourlyRevenue = [];
        $hourlyOrders = [];
        for ($h = 7; $h <= 17; $h++) {
            $hourlyLabels[] = sprintf('%d:00', $h > 12 ? $h - 12 : $h) . ($h >= 12 ? ' PM' : ' AM');
            $hourlyRevenue[] = (float) ($hourlySales[$h]->revenue ?? 0);
            $hourlyOrders[] = (int) ($hourlySales[$h]->orders ?? 0);
        }

        // Stock distribution for all products (doughnut)
        $stockDistribution = [
            'Critical (0-5)' => Product::where('is_available', true)->where('stock', '<=', 5)->count(),
            'Low (6-15)' => Product::where('is_available', true)->whereBetween('stock', [6, 15])->count(),
            'Healthy (16+)' => Product::where('is_available', true)->where('stock', '>', 15)->count(),
        ];

        return view('livewire.staff.dashboard', [
            'orders' => $orders,
            'todayStats' => $todayStats,
            'topProducts' => $topProducts,
            'hourlyLabels' => $hourlyLabels,
            'hourlyRevenue' => $hourlyRevenue,
            'hourlyOrders' => $hourlyOrders,
            'stockDistribution' => $stockDistribution,
        ]);
    }
}
