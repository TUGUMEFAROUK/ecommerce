# Payments setup

This store supports two payment methods:

- **Cash on delivery (cod)** — always available, default.
- **Card & online payments (flutterwave)** — hosted checkout via [Flutterwave](https://developer.flutterwave.com). The customer is redirected to Flutterwave's hosted payment page; card data never touches this server.

The store stays cash-on-delivery-only until you configure credentials below. Nothing calls Flutterwave while the keys are empty.

## How an online payment is completed

1. The customer picks "Card & online payments" at checkout. An order and a pending `Payment` row (provider `flutterwave`, a generated `tx_ref`) are created.
2. The customer is redirected to the hosted payment page.
3. Flutterwave sends a webhook to `POST /webhooks/flutterwave` and the customer returns to the confirmation page.
4. The webhook is only trusted if its `verif-hash` header matches `FLW_SECRET_HASH` **and** a server-side verify call (`GET /v3/transactions/{id}/verify`) confirms the transaction's status, amount, currency, and tx_ref against the stored payment. Only then is the payment marked succeeded and the order moved to *paid*.
5. Redirecting back never marks anything paid on its own; a redelivered webhook is a no-op.

## Configuration

Set these in `.env` (see `.env.example`):

| Variable | Meaning |
| --- | --- |
| `PAYMENTS_DRIVER` | Default gateway: `cod` (default) or `flutterwave`. |
| `FLW_PUBLIC_KEY` | Public key from the Flutterwave dashboard. |
| `FLW_SECRET_KEY` | Secret key from the Flutterwave dashboard. |
| `FLW_SECRET_HASH` | Any secret you invent; must match the value configured on the webhook URL in the Flutterwave dashboard. |
| `FLW_BASE_URL` | Optional; defaults to `https://api.flutterwave.com/v3`. |

`config/payments.php` controls behaviour (driver, allowed currencies); `config/services.php` holds the credentials.

The Flutterwave option appears at checkout only when all three keys are set and the store currency (`store_currency` in admin settings, default USD) is in `config('payments.flutterwave.currencies')`. If a customer posts `payment_method=flutterwave` while it is unavailable, checkout fails with a validation error — it never silently falls back to another method.

## Going live checklist

1. Create a Flutterwave account and complete merchant onboarding.
2. Put the **live** keys in `.env` (not the test keys) and set `PAYMENTS_DRIVER=flutterwave`.
3. In the Flutterwave dashboard, register the webhook URL `https://your-domain.com/webhooks/flutterwave` and set its secret hash to the same value as `FLW_SECRET_HASH`.
4. Serve the site over HTTPS (payment providers require it).
5. Do a real low-value transaction end-to-end and confirm the order moves to *paid* and the payment row records the transaction id.

Until all of the above is done the integration is code-complete but not live-tested; the maintainers have not moved real money with it.

## Notes for developers

- Gateways live behind `App\Payments\PaymentGateway` (`CodGateway`, `FlutterwaveGateway`) and are resolved through `App\Payments\PaymentGatewayManager`.
- Webhook processing lives in `App\Services\PaymentWebhookService`; event deduplication is the unique `(provider, event_id)` key on `payment_webhook_events`.
- The bundled `flutterwavedev/flutterwave-v3` composer package is installed but not used at runtime; `App\Payments\FlutterwaveApiClient` sends the same REST calls via Laravel's HTTP client (the SDK's fork logs into `vendor/` and fires telemetry during construction). Remove the package if you prefer.
- Tests fake the provider with `Http::fake()` and never hit the network.
