<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PayMongoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('Order Confirmed')]
class OrderConfirmed extends Component
{
    public Order $order;

    public function mount(Order $order, PayMongoService $payMongoService): void
    {
        abort_unless(auth()->id() === $order->user_id, 403);

        // If the order is still pending and has a checkout session,
        // poll PayMongo directly to confirm payment status.
        // This handles the case where the webhook hasn't fired yet
        // (local dev, webhook delay, or missed delivery).
        if ($order->status === 'pending' && $order->paymongo_checkout_id) {
            try {
                $paid = $payMongoService->confirmCheckoutSession($order);
                if ($paid) {
                    $order->refresh();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('OrderConfirmed: could not poll PayMongo', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function render()
    {
        $this->order->refresh();
        return view('livewire.order-confirmed', ['order' => $this->order]);
    }
}
