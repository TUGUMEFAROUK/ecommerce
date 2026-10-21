<?php

namespace App\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for Flutterwave's documented REST API.
 *
 * The bundled flutterwave-v3 SDK was evaluated and rejected: its fork of
 * the php-http stack builds a singleton config that logs to a file inside
 * vendor/ and fires telemetry HTTP requests during construction, which is
 * hostile to a Laravel container and to tests. This client sends exactly
 * the same requests the SDK would (Bearer secret key, JSON bodies) using
 * Laravel's HTTP client, which tests can intercept with Http::fake().
 */
class FlutterwaveApiClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::withToken((string) config('services.flutterwave.secret_key'))
            ->acceptJson()
            ->baseUrl(rtrim((string) config('payments.flutterwave.base_url', 'https://api.flutterwave.com/v3'), '/'))
            ->connectTimeout(5)
            ->timeout(15);
    }

    /**
     * Create a hosted payment page (POST /v3/payments).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null The provider's `data` object, or null on failure
     */
    public function createPayment(array $payload): ?array
    {
        $response = $this->http->post('payments', $payload);

        if (! $response->successful() || $response->json('status') !== 'success') {
            return null;
        }

        $data = $response->json('data');

        return is_array($data) ? $data : null;
    }

    /**
     * Verify a transaction server-side (GET /v3/transactions/{id}/verify).
     *
     * @return array<string, mixed>|null The provider's `data` object, or null on failure
     */
    public function verifyTransaction(string $transactionId): ?array
    {
        $response = $this->http->get('transactions/'.rawurlencode($transactionId).'/verify');

        if (! $response->successful() || $response->json('status') !== 'success') {
            return null;
        }

        $data = $response->json('data');

        return is_array($data) ? $data : null;
    }
}
