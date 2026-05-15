<?php

namespace App\Http\Controllers;

use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    /**
     * Receives and processes incoming webhook events from PayMongo.
     * This endpoint is called server-to-server by PayMongo — not by the student's browser.
     * It is registered outside the 'web' middleware group so it has no CSRF protection
     * (intentional — PayMongo can't send a CSRF token).
     *
     * Flow:
     * 1. Read the raw request body and the PayMongo-Signature header.
     * 2. Verify the signature using the webhook secret to confirm the request is genuine.
     *    If verification fails, return 400 and log a warning.
     * 3. If the event is 'checkout_session.payment.paid', mark the order as paid.
     * 4. Return 200 so PayMongo knows the webhook was received successfully.
     */
    public function __invoke(Request $request, PayMongoService $payMongoService): Response
    {
        // Get the raw JSON body — must be raw (not parsed) for signature verification
        $payload = $request->getContent();

        // PayMongo sends this header so we can verify the request is authentic
        $signatureHeader = $request->header('Paymongo-Signature', '');

        // Verify the signature — throws SignatureVerificationException if invalid
        try {
            $event = $payMongoService->verifyWebhook($payload, $signatureHeader);
        } catch (\Paymongo\Exceptions\SignatureVerificationException $e) {
            Log::warning('PayMongo webhook: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature.', 400);
        }

        Log::info('PayMongo webhook received', [
            'event_type' => $event->type ?? 'unknown',
            'event_id' => $event->id ?? 'unknown',
        ]);

        // The only event we care about — payment was successfully completed
        if (($event->type ?? '') === 'checkout_session.payment.paid') {
            $payMongoService->handlePaymentPaid($event);
        }

        // Always return 200 so PayMongo doesn't retry the webhook
        return response('Webhook handled.', 200);
    }
}
