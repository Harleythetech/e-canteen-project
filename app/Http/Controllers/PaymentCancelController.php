<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayMongoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCancelController extends Controller
{
    /**
     * Handles the cancel_url redirect from PayMongo.
     * This is called when the student closes or cancels the PayMongo payment page.
     *
     * Flow:
     * 1. Make sure only the order owner can trigger this (403 otherwise).
     * 2. Poll PayMongo one final time — sometimes PayMongo redirects to cancel_url
     *    even after a successful payment (rare race condition). If payment is confirmed,
     *    send the student to the confirmation page instead of cancelling.
     * 3. If the order is still pending after polling, cancel it and restore stock,
     *    then redirect back to checkout with the cart restored.
     * 4. If the order is already paid or in another state, just show the order status page.
     */
    public function __invoke(Request $request, Order $order, PayMongoService $payMongoService): RedirectResponse
    {
        // Only the order owner can cancel
        abort_unless(auth()->id() === $order->user_id, 403);

        // Before cancelling, do a final poll — in case the user actually paid
        // and PayMongo redirected to cancel_url by mistake (rare but possible)
        if ($order->status === 'pending' && $order->paymongo_checkout_id) {
            try {
                $paid = $payMongoService->confirmCheckoutSession($order);
                if ($paid) {
                    // Payment went through — send to confirmation instead of cancelling
                    $order->refresh();
                    return redirect()->route('orders.confirmed', $order)
                        ->with('success', 'Payment confirmed!');
                }
            } catch (\Throwable $e) {
                // Polling failed — not fatal, just log it and proceed with cancellation
                Log::warning('PaymentCancel: polling failed', [
                    'order_number' => $order->order_number,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        // Cancel the order if it's still pending and restore product stock
        if ($order->status === 'pending') {
            $order->load('items')->cancelAndRestoreStock();

            return redirect()->route('checkout')
                ->with('payment_cancelled', 'Your payment was cancelled. Your cart has been restored.');
        }

        // Order is already paid or in another non-cancellable state — just show its status
        return redirect()->route('orders.show', $order);
    }
}
