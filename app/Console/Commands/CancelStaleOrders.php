<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelStaleOrders extends Command
{
    protected $signature   = 'orders:cancel-stale {--minutes=30 : Minutes before a pending order is considered stale}';
    protected $description = 'Cancel pending orders that have not been paid within the timeout window and restore their stock.';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $cutoff  = now()->subMinutes($minutes);

        $stale = Order::where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->with('items')
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale orders found.');
            return self::SUCCESS;
        }

        foreach ($stale as $order) {
            $order->cancelAndRestoreStock();
            $this->line("  Cancelled: {$order->order_number}");
        }

        $this->info("Cancelled {$stale->count()} stale order(s).");
        return self::SUCCESS;
    }
}
