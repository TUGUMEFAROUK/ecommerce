<?php

namespace App\Http\Controllers;

use App\Services\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentWebhookController extends Controller
{
    /**
     * Handle a Flutterwave webhook delivery.
     *
     * Authenticates the delivery via the verif-hash secret, then hands it
     * to the webhook service, which re-verifies the transaction with the
     * provider API before touching any money-moving state. Always answers
     * 200 once the signature checks out so the provider stops retrying.
     */
    public function __invoke(Request $request, PaymentWebhookService $service): JsonResponse
    {
        $secretHash = (string) config('services.flutterwave.secret_hash', '');
        $signature = (string) $request->header('verif-hash', '');

        if ($secretHash === '' || $signature === '' || ! hash_equals($secretHash, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->all();
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $eventType = (string) ($payload['event'] ?? '');

        // Flutterwave payloads carry no dedicated event id, so derive a
        // stable one from the event type and the transaction id; that way
        // redeliveries of the same event deduplicate while distinct
        // events for the same transaction do not.
        $eventId = isset($data['id']) && is_numeric($data['id'])
            ? sprintf('%s:%s', $eventType, $data['id'])
            : sprintf('%s:%s', $eventType, sha1((string) $request->getContent()));

        try {
            $service->handle('flutterwave', $eventId, $eventType, $payload);
        } catch (Throwable $exception) {
            // Answering non-2xx makes the provider retry, which is what
            // we want for transient failures, but keep a log line so a
            // failing webhook does not disappear silently.
            Log::error('Flutterwave webhook processing failed.', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        return response()->json(['status' => 'received']);
    }
}
