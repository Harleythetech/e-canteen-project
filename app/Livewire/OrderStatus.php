<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PayMongoService;
use App\Services\QrCodeService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('Order Status')]
class OrderStatus extends Component
{
    // The order being viewed — injected via route model binding
    public Order $order;

    /**
     * Runs when the student opens the order detail page.
     *
     * Checks authorization via OrderPolicy (owner, staff, or admin can view).
     * Also polls PayMongo if the order is still pending — same reason as
     * OrderConfirmed: the webhook may not have arrived yet.
     */
    public function mount(Order $order, PayMongoService $payMongoService): void
    {
        // Enforces OrderPolicy::view() — only the owner, staff, or admin can see this
        $this->authorize('view', $order);
        $this->order = $order->load('items');

        // If the order is still pending and has a checkout session,
        // poll PayMongo to see if payment went through.
        if ($order->status === 'pending' && $order->paymongo_checkout_id) {
            try {
                $paid = $payMongoService->confirmCheckoutSession($order);
                if ($paid) {
                    $this->order->refresh();
                }
            } catch (\Throwable) {
                // Non-fatal — order status stays as-is, student can refresh manually
            }
        }
    }

    /**
     * Cancels the order if it's still in a cancellable state (pending only).
     * Enforces OrderPolicy::cancel() — only the owner or admin can cancel.
     * Restores stock for all items after cancellation.
     */
    public function cancelOrder(): void
    {
        $this->authorize('cancel', $this->order);

        if ($this->order->canTransitionTo('cancelled')) {
            $this->order->load('items')->cancelAndRestoreStock();
            $this->order->refresh();
            $this->dispatch('toast', type: 'info', message: 'Your order has been cancelled.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'This order cannot be cancelled.');
        }
    }

    /**
     * Renders the order status page.
     * Refreshes the order on every render so the status badge stays current.
     * Generates a QR code SVG only when the order is paid, preparing, or ready —
     * the QR code is what staff scan to mark the order as completed/picked up.
     */
    public function render(QrCodeService $qrCodeService)
    {
        $this->order->refresh();

        // Only show the QR code when the order is active and ready to be scanned
        $qrSvg = in_array($this->order->status, ['paid', 'preparing', 'ready'])
            ? $qrCodeService->generateSvg($this->order->order_number)
            : null;

        return view('livewire.order-status', ['qrSvg' => $qrSvg]);
    }
}
