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
    // Synced to the URL — controls which tab is visible: 'orders', 'stats', or 'scanner'
    #[Url]
    public string $tab = 'orders';

    // Controls which orders are shown in the queue: 'active', 'pending', 'paid', etc.
    public string $statusFilter = 'active';

    // Holds the raw value from the QR scanner input before processing
    public string $scannedCode = '';

    /**
     * Called by the frontend every 5 seconds via setInterval to refresh the order list.
     * The method body is intentionally empty — just calling it triggers a Livewire re-render
     * which reloads the orders from the database without resetting any Alpine.js state.
     */
    public function refreshOrders(): void
    {
        // Triggers a re-render without resetting any state.
    }

    /**
     * Advances an order to its next status in the state machine.
     * Called when staff clicks the action button on an order card.
     *
     * Status progression: pending → paid → preparing → ready → completed
     * Enforces OrderPolicy::updateStatus() — only staff and admin can do this.
     */
    public function advanceOrder(int $orderId): void
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('updateStatus', $order);

        // Determine what the next status should be based on the current one
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

    /**
     * Processes a QR code scanned by the staff camera.
     * The scanned value should be an order number (e.g. ORD-XXXXXXXXX).
     *
     * - If the order is 'ready', marks it as 'completed' (pickup confirmed).
     * - If the order is already 'completed', shows an info message.
     * - If the order is in any other state, shows an error (not ready yet).
     *
     * Clears the scanned code after processing so the scanner is ready for the next scan.
     */
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
            // Student is here to pick up — mark as completed
            $order->transitionTo('completed');
            $this->dispatch('scanner-result', type: 'success', message: "Order {$order->order_number} completed!");
        } elseif ($order->status === 'completed') {
            $this->dispatch('scanner-result', type: 'info', message: "Order {$order->order_number} was already completed.");
        } else {
            // Order exists but isn't ready yet — staff shouldn't complete it
            $this->dispatch('scanner-result', type: 'error', message: "Order {$order->order_number} is not ready for pickup (status: {$order->status}).");
        }

        $this->scannedCode = '';
    }

    /**
     * Loads all data needed to render the staff dashboard.
     *
     * - orders: the live order queue, filtered by statusFilter
     * - todayStats: summary numbers for the top stats bar
     * - topProducts: best-selling items today (for the products tab)
     * - hourlyLabels/Revenue/Orders: data for the hourly sales line chart
     * - stockDistribution: data for the stock health doughnut chart
     * - lowStockProducts: items with 5 or fewer units remaining
     */
    public function render()
    {
        // Load orders with their items and the student's name for the order cards
        $orders = Order::with(['items', 'user'])
            ->when($this->statusFilter === 'active', fn($q) => $q->active())
            ->when($this->statusFilter === 'pending', fn($q) => $q->pending())
            ->when($this->statusFilter === 'paid', fn($q) => $q->paid())
            ->when($this->statusFilter === 'preparing', fn($q) => $q->preparing())
            ->when($this->statusFilter === 'ready', fn($q) => $q->ready())
            ->latest()
            ->get();

        // Summary stats shown at the top of the dashboard
        $todayStats = [
            'total_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::whereDate('created_at', today())->whereNotNull('paid_at')->sum('total'),
            'pending_orders' => Order::active()->count(),
            'completed_today' => Order::completed()->whereDate('completed_at', today())->count(),
        ];

        // Top 10 products sold today (only shows items with at least 1 sale)
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

        // Hourly sales data for today — used by the line chart (7 AM to 5 PM)
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

        // Stock health breakdown — used by the doughnut chart
        $stockDistribution = [
            'Critical (0-5)' => Product::where('stock', '<=', 5)->count(),
            'Low (6-15)'     => Product::whereBetween('stock', [6, 15])->count(),
            'Healthy (16+)'  => Product::where('stock', '>', 15)->count(),
        ];

        // Products that need restocking soon
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->get();

        return view('livewire.staff.dashboard', [
            'orders' => $orders,
            'todayStats' => $todayStats,
            'topProducts' => $topProducts,
            'hourlyLabels' => $hourlyLabels,
            'hourlyRevenue' => $hourlyRevenue,
            'hourlyOrders' => $hourlyOrders,
            'stockDistribution' => $stockDistribution,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
