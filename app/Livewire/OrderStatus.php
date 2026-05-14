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
    public Order $order;

    public function mount(Order $order, PayMongoService $payMongoService): void
    {
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
                // Non-fatal — order status stays as-is
            }
        }
    }

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

    public function render(QrCodeService $qrCodeService)
    {
        $this->order->refresh();

        $qrSvg = in_array($this->order->status, ['paid', 'preparing', 'ready'])
            ? $qrCodeService->generateSvg($this->order->order_number)
            : null;

        return view('livewire.order-status', ['qrSvg' => $qrSvg]);
    }
}
