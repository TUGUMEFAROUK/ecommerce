<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Payments\CheckoutException;
use App\Payments\PaymentGatewayManager;
use App\Services\CartPricingService;
use App\Services\CartService;
use App\Services\SettingsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the checkout form with a server-side price quote.
     */
    public function show(Request $request, CartService $carts, CartPricingService $pricing, SettingsService $settings): View|RedirectResponse
    {
        $lines = $carts->lines($request->user());

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $problems = $carts->validateForCheckout($lines);

        if ($problems !== []) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => implode(' ', $problems)]);
        }

        $flutterwave = app(PaymentGatewayManager::class)->driver('flutterwave');
        $currency = Str::upper($settings->get('store_currency'));

        return view('checkout.show', [
            'lines' => $lines,
            'quote' => $pricing->quote($lines),
            'showFlutterwave' => $flutterwave->isConfigured() && $flutterwave->supportsCurrency($currency),
        ]);
    }

    /**
     * Create the order from the current cart contents.
     *
     * Stock, product status, and prices are re-read under row locks inside
     * the transaction; every total is recomputed from the database and the
     * cart is only cleared once the order has been committed.
     */
    public function store(CheckoutRequest $request, CartService $carts, CartPricingService $pricing, SettingsService $settings): RedirectResponse
    {
        $user = $request->user();
        $currency = Str::upper($settings->get('store_currency'));
        $paymentMethod = (string) $request->validated('payment_method');

        // Fold any session cart into the database cart before locking rows.
        $carts->lines($user);

        $order = DB::transaction(function () use ($request, $user, $carts, $pricing, $currency, $paymentMethod): Order {
            $cart = Cart::query()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            $lines = $cart === null
                ? collect()
                : $cart->items()
                    ->with('product')
                    ->get()
                    ->map(fn ($item): ?array => $item->product === null
                        ? null
                        : ['product' => $item->product, 'quantity' => $item->quantity])
                    ->filter()
                    ->values();

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $lines->pluck('product.id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = $lines
                ->map(fn (array $line): ?array => isset($products[$line['product']->id])
                    ? ['product' => $products[$line['product']->id], 'quantity' => $line['quantity']]
                    : null)
                ->filter()
                ->values();

            $problems = $carts->validateForCheckout($lines);

            if ($problems !== []) {
                throw ValidationException::withMessages([
                    'cart' => implode(' ', $problems),
                ]);
            }

            $quote = $pricing->quote($lines);

            $order = Order::create([
                'user_id' => $user->getKey(),
                'status' => OrderStatus::Pending,
                'currency' => $currency,
                'total' => $quote['total'],
                'contact_name' => $request->validated('contact_name'),
                'contact_email' => $request->validated('contact_email'),
                'contact_phone' => $request->validated('contact_phone'),
                'shipping_line1' => $request->validated('shipping_line1'),
                'shipping_line2' => $request->validated('shipping_line2'),
                'shipping_city' => $request->validated('shipping_city'),
                'shipping_state' => $request->validated('shipping_state'),
                'shipping_postal_code' => $request->validated('shipping_postal_code'),
                'shipping_country' => $request->validated('shipping_country'),
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];

                OrderItem::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $line['quantity'],
                    'line_total' => $pricing->lineTotal($product->price, $line['quantity']),
                ]);

                $product->decrement('stock_quantity', $line['quantity']);
            }

            // Cash on delivery is a manual option: the payment stays pending
            // until it is actually collected, never marked successful here.
            // Online payments start as pending too; only a verified webhook
            // can ever mark them succeeded.
            Payment::create([
                'order_id' => $order->getKey(),
                'provider' => $paymentMethod === 'flutterwave' ? 'flutterwave' : 'cod',
                'amount' => $quote['total'],
                'currency' => $currency,
                'status' => PaymentStatus::Pending,
            ]);

            $cart->items()->delete();

            return $order;
        });

        if ($paymentMethod === 'cod') {
            return redirect()->route('checkout.confirmation', $order);
        }

        return $this->startFlutterwaveCheckout($order);
    }

    /**
     * Re-open the hosted checkout for an order whose online payment is
     * still pending (the customer abandoned the payment page, or their
     * first attempt failed).
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $payment = $order->payments()
            ->where('provider', 'flutterwave')
            ->where('status', PaymentStatus::Pending)
            ->first();

        if ($payment === null) {
            return redirect()
                ->route('checkout.confirmation', $order)
                ->with('status', 'This order has no pending payment.');
        }

        return $this->startFlutterwaveCheckout($order);
    }

    /**
     * Send the customer to Flutterwave's hosted payment page.
     *
     * The order is already committed at this point; if the provider
     * cannot start a session the customer is told so and can retry from
     * the confirmation page rather than the order being lost.
     */
    private function startFlutterwaveCheckout(Order $order): RedirectResponse
    {
        $payment = $order->payments()
            ->where('provider', 'flutterwave')
            ->firstOrFail();

        try {
            $session = app(PaymentGatewayManager::class)
                ->driver('flutterwave')
                ->createCheckout($order);
        } catch (CheckoutException) {
            throw ValidationException::withMessages([
                'payment_method' => 'The payment provider could not start the checkout. Your order has been saved — use "Complete payment" on the confirmation page to try again.',
            ]);
        }

        $payment->update(['provider_reference' => $session->reference]);

        return redirect()->away($session->redirectUrl);
    }

    /**
     * Show the order confirmation page after checkout.
     */
    public function confirmation(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('checkout.confirmation', [
            'order' => $order->load(['items', 'payments']),
        ]);
    }
}
