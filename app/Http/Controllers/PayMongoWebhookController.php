<?php

namespace App\Http\Controllers;

use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(Request $request, PayMongoService $payMongoService): Response
    {
        $payload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature', '');

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

        if (($event->type ?? '') === 'checkout_session.payment.paid') {
            $payMongoService->handlePaymentPaid($event);
        }

        return response('Webhook handled.', 200);
    }
}
