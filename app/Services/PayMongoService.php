<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    private \Paymongo\PaymongoClient $client;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new \Paymongo\PaymongoClient(config('paymongo.secret_key'));
        $this->baseUrl = config('paymongo.base_url');
    }

    /**
     * Create a PayMongo Checkout Session via the REST API.
     * The official SDK does not include checkout session support.
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $lineItems = $order->items->map(fn($item) => [
            'amount' => (int) ($item->unit_price * 100), // centavos
            'currency' => 'PHP',
            'name' => $item->product_name,
            'quantity' => $item->quantity,
        ])->toArray();

        $response = Http::withBasicAuth(config('paymongo.secret_key'), '')
            ->timeout(30)
            ->post("{$this->baseUrl}/checkout_sessions", [
                'data' => [
                    'attributes' => [
                        'line_items' => $lineItems,
                        'payment_method_types' => ['gcash', 'card', 'grab_pay', 'paymaya'],
                        'success_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                        'description' => "PLSP E-Canteen Order {$order->order_number}",
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'show_line_items' => true,
                        'metadata' => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::error('PayMongo checkout session creation failed', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new \RuntimeException('Failed to create payment session. Please try again.');
        }

        $data = $response->json('data');

        return [
            'checkout_id' => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url'],
        ];
    }

    /**
     * Poll PayMongo to check if a checkout session has been paid.
     * Used as a reliable fallback when the webhook hasn't fired yet.
     * Returns true if the order was updated to paid.
     */
    public function confirmCheckoutSession(Order $order): bool
    {
        $response = Http::withBasicAuth(config('paymongo.secret_key'), '')
            ->timeout(15)
            ->get("{$this->baseUrl}/checkout_sessions/{$order->paymongo_checkout_id}");

        if (!$response->successful()) {
            Log::warning('PayMongo: failed to fetch checkout session', [
                'order_number' => $order->order_number,
                'status'       => $response->status(),
                'body'         => $response->json(),
            ]);
            return false;
        }

        $attributes = $response->json('data.attributes');

        // Log the full attributes so we can see exactly what PayMongo returns
        Log::info('PayMongo: checkout session polled', [
            'order_number'   => $order->order_number,
            'checkout_id'    => $order->paymongo_checkout_id,
            'status'         => $attributes['status'] ?? null,
            'payment_status' => $attributes['payment_status'] ?? null,
            'payments_count' => count($attributes['payments'] ?? []),
        ]);

        // PayMongo checkout session is paid when:
        // - status = 'completed'  (documented but not always set)
        // - payment_status = 'paid'  (not always present)
        // - payments array has at least one entry with status 'paid'
        //   (this is the reliable signal — confirmed via test logs)
        $sessionStatus  = $attributes['status'] ?? null;
        $paymentStatus  = $attributes['payment_status'] ?? null;
        $payments       = $attributes['payments'] ?? [];
        $firstPayStatus = $payments[0]['attributes']['status'] ?? $payments[0]['status'] ?? null;

        $isPaid = $sessionStatus === 'completed'
            || $paymentStatus === 'paid'
            || $firstPayStatus === 'paid';

        if (!$isPaid) {
            return false;
        }

        // Already processed — idempotent
        if ($order->paid_at !== null) {
            return false;
        }

        $paymentId     = $payments[0]['id'] ?? null;
        $paymentMethod = $attributes['payment_method_used'] ?? null;

        $order->update([
            'status'               => 'paid',
            'paid_at'              => now(),
            'paymongo_payment_id'  => $paymentId,
            'payment_method'       => $paymentMethod,
        ]);

        Log::info('PayMongo: payment confirmed via polling', [
            'order_number'   => $order->order_number,
            'payment_id'     => $paymentId,
            'payment_method' => $paymentMethod,
        ]);

        return true;
    }

    /**
     * Verify webhook signature and parse event using the official SDK.
     */
    public function verifyWebhook(string $payload, string $signatureHeader): object
    {
        return $this->client->webhooks->constructEvent([
            'payload' => $payload,
            'signature_header' => $signatureHeader,
            'webhook_secret_key' => config('paymongo.webhook_secret'),
        ]);
    }

    /**
     * Process a successful payment event.
     * Returns true if the order was updated, false if already processed (idempotent).
     */
    public function handlePaymentPaid(object $event): bool
    {
        // The PayMongo webhook event wraps the checkout session under data.attributes
        // Try multiple paths since SDK versions differ in how they expose the payload
        $checkoutId = $event->data->id
            ?? $event->data->attributes->data->id
            ?? $event->resource['data']['id']
            ?? null;

        if (!$checkoutId) {
            Log::warning('PayMongo webhook: missing checkout ID in event', [
                'event_id' => $event->id ?? 'unknown',
                'event_data' => json_encode($event),
            ]);
            return false;
        }

        $order = Order::where('paymongo_checkout_id', $checkoutId)->first();

        if (!$order) {
            Log::warning('PayMongo webhook: order not found for checkout', [
                'checkout_id' => $checkoutId,
            ]);
            return false;
        }

        // Idempotency: skip if already paid
        if ($order->paid_at !== null) {
            Log::info('PayMongo webhook: order already paid, skipping', [
                'order_number' => $order->order_number,
            ]);
            return false;
        }

        // Extract payment details — try both SDK object and array access
        $payments = $event->data->attributes->payments
            ?? $event->resource['data']['attributes']['payments']
            ?? [];

        $paymentId = is_array($payments) ? ($payments[0]['id'] ?? null) : ($payments[0]->id ?? null);

        $paymentMethod = $event->data->attributes->payment_method_used
            ?? $event->resource['data']['attributes']['payment_method_used']
            ?? null;

        $order->update([
            'status'              => 'paid',
            'paid_at'             => now(),
            'paymongo_payment_id' => $paymentId,
            'payment_method'      => $paymentMethod,
        ]);

        Log::info('PayMongo webhook: payment confirmed', [
            'order_number' => $order->order_number,
            'payment_id'   => $paymentId,
        ]);

        return true;
    }
}
