<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Payments\PaymentGatewayManager;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Applies payment provider webhooks to payments and orders.
 *
 * A webhook alone never fulfils an order: the handler re-verifies the
 * transaction with the provider's API and checks status, amount,
 * currency, and tx_ref against the stored payment before any money-moving
 * state change. Events are idempotent via the (provider, event_id) unique
 * key, so provider redeliveries are a no-op.
 */
class PaymentWebhookService
{
    public function __construct(private readonly PaymentGatewayManager $gateways) {}

    /**
     * Record the event (idempotently) and apply it.
     */
    public function handle(string $provider, string $eventId, string $eventType, array $payload): void
    {
        try {
            $event = PaymentWebhookEvent::query()->create([
                'provider' => $provider,
                'event_id' => $eventId,
                'event_type' => $eventType,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A redelivery of an event we have already processed.
            return;
        }

        match ($eventType) {
            'charge.completed' => $this->handleChargeCompleted($provider, $payload),
            'charge.failed' => $this->handleChargeFailed($provider, $payload),
            default => null,
        };

        $event->update(['processed_at' => now()]);
    }

    private function handleChargeCompleted(string $provider, array $payload): void
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $transactionId = isset($data['id']) ? (string) $data['id'] : '';
        $txRef = isset($data['tx_ref']) ? (string) $data['tx_ref'] : '';

        $payment = $this->findPayment($provider, $transactionId, $txRef);

        if ($payment === null || $payment->status !== PaymentStatus::Pending) {
            return;
        }

        $verified = $this->gateways
            ->driver($provider)
            ->verifyTransaction($transactionId !== '' ? $transactionId : (string) $payment->provider_transaction_id);

        if ($verified === null || ! $this->verificationMatches($payment, $verified)) {
            // Unverified or mismatched: leave the payment pending rather
            // than fulfil an order on the strength of a webhook body alone.
            return;
        }

        DB::transaction(function () use ($payment, $transactionId): void {
            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'paid_at' => now(),
                'provider_transaction_id' => $payment->provider_transaction_id ?? ($transactionId !== '' ? $transactionId : null),
            ]);

            $order = $payment->order;

            if ($order->status === OrderStatus::Pending) {
                app(OrderStatusService::class)->transition(
                    $order,
                    OrderStatus::Paid,
                    'Confirmed by '.$payment->provider.' webhook',
                    null,
                );
            }
        });
    }

    private function handleChargeFailed(string $provider, array $payload): void
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $payment = $this->findPayment(
            $provider,
            isset($data['id']) ? (string) $data['id'] : '',
            isset($data['tx_ref']) ? (string) $data['tx_ref'] : '',
        );

        if ($payment === null || $payment->status !== PaymentStatus::Pending) {
            return;
        }

        $payment->update(['status' => PaymentStatus::Failed]);
    }

    private function findPayment(string $provider, string $transactionId, string $txRef): ?Payment
    {
        if ($transactionId !== '') {
            $payment = Payment::query()
                ->where('provider', $provider)
                ->where('provider_transaction_id', $transactionId)
                ->first();

            if ($payment !== null) {
                return $payment;
            }
        }

        if ($txRef !== '') {
            return Payment::query()
                ->where('provider', $provider)
                ->where('provider_reference', $txRef)
                ->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $verified
     */
    private function verificationMatches(Payment $payment, array $verified): bool
    {
        $verifiedStatus = (string) ($verified['status'] ?? '');
        $verifiedAmountCents = (int) round(((float) ($verified['amount'] ?? 0)) * 100);
        $verifiedCurrency = Str::upper((string) ($verified['currency'] ?? ''));
        $verifiedTxRef = (string) ($verified['tx_ref'] ?? '');

        return $verifiedStatus === 'successful'
            && $verifiedAmountCents === (int) round(((float) $payment->amount) * 100)
            && $verifiedCurrency === Str::upper((string) $payment->currency)
            && ($verifiedTxRef === '' || $verifiedTxRef === $payment->provider_reference);
    }
}
