<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayMongoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCancelController extends Controller
{
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
                    $order->refresh();
                    return redirect()->route('orders.confirmed', $order)
                        ->with('success', 'Payment confirmed!');
                }
            } catch (\Throwable $e) {
                Log::warning('PaymentCancel: polling failed', [
                    'order_number' => $order->order_number,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        // Cancel the order if it's still pending
        if ($order->status === 'pending') {
            $order->load('items')->cancelAndRestoreStock();

            return redirect()->route('checkout')
                ->with('payment_cancelled', 'Your payment was cancelled. Your cart has been restored.');
        }

        // Already paid or in another state — just send to order status
        return redirect()->route('orders.show', $order);
    }
}
