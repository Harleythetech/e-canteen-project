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
    // The order being confirmed — injected via route model binding
    public Order $order;

    /**
     * Runs when the student lands on the confirmation page after paying.
     *
     * The webhook from PayMongo may not have arrived yet (especially in local dev
     * where webhooks can't reach localhost). So we poll PayMongo directly here
     * to check if the payment went through and update the order status if needed.
     *
     * Also enforces ownership — a student can't view another student's confirmation.
     */
    public function mount(Order $order, PayMongoService $payMongoService): void
    {
        // Only the student who placed this order can see this page
        abort_unless(auth()->id() === $order->user_id, 403);

        // If the order is still pending and has a checkout session,
        // poll PayMongo directly to confirm payment status.
        // This handles the case where the webhook hasn't fired yet
        // (local dev, webhook delay, or missed delivery).
        if ($order->status === 'pending' && $order->paymongo_checkout_id) {
            try {
                $paid = $payMongoService->confirmCheckoutSession($order);
                if ($paid) {
                    // Payment confirmed — refresh the model so the view shows 'paid'
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

    /**
     * Refreshes the order on every render so the status stays up to date
     * (in case the webhook arrives while the student is on this page).
     */
    public function render()
    {
        $this->order->refresh();
        return view('livewire.order-confirmed', ['order' => $this->order]);
    }
}
