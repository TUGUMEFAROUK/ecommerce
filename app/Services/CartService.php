<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Session key holding the guest (unauthenticated) cart as product id => quantity.
     */
    private const SESSION_KEY = 'guest_cart';

    /**
     * The maximum quantity of a single product that may sit in the cart.
     */
    public const MAX_PER_ITEM = 10;

    /**
     * Resolve the current cart lines for a user or guest.
     *
     * Guest carts live in the session. For authenticated customers any
     * session cart is merged into their database cart the first time it is
     * resolved after logging in, so a guest's items survive authentication.
     *
     * @return Collection<int, array{product: Product, quantity: int}>
     */
    public function lines(?User $user): Collection
    {
        if ($user === null) {
            return $this->sessionLines();
        }

        $this->mergeSessionCartIntoDatabase($user);

        $cart = Cart::query()->where('user_id', $user->getKey())->first();

        if ($cart === null) {
            return collect();
        }

        return $cart->items()
            ->with('product')
            ->get()
            ->map(fn (CartItem $item): ?array => $item->product === null
                ? null
                : ['product' => $item->product, 'quantity' => $item->quantity])
            ->filter()
            ->values();
    }

    /**
     * Total item quantity across all cart lines.
     *
     * The badge only needs a number, so the count is aggregated in the
     * database instead of hydrating every cart item and product model.
     */
    public function count(?User $user): int
    {
        if ($user === null) {
            return $this->sessionCount();
        }

        return (int) CartItem::query()
            ->whereIn('cart_id', Cart::query()
                ->where('user_id', $user->getKey())
                ->select('id'))
            ->sum('quantity');
    }

    /**
     * Add a quantity to the cart, clamping the combined quantity to the
     * available stock and the per-item cap.
     */
    public function add(?User $user, Product $product, int $quantity): void
    {
        if ($user === null) {
            $session = $this->sessionCart();
            $existing = $session[$product->getKey()] ?? 0;

            $session[$product->getKey()] = $this->combinedQuantity($existing, $quantity, $product);

            session()->put(self::SESSION_KEY, $session);

            return;
        }

        $cart = Cart::query()->firstOrCreate(['user_id' => $user->getKey()]);

        $item = $cart->items()->where('product_id', $product->getKey())->first();
        $existing = $item?->quantity ?? 0;
        $combined = $this->combinedQuantity($existing, $quantity, $product);

        if ($combined < 1) {
            return;
        }

        if ($item !== null) {
            $item->update(['quantity' => $combined]);
        } else {
            $cart->items()->create([
                'product_id' => $product->getKey(),
                'quantity' => $combined,
            ]);
        }
    }

    /**
     * Set the quantity for a cart line. The caller is responsible for
     * validating the product's status and the quantity against stock.
     */
    public function updateQuantity(?User $user, Product $product, int $quantity): void
    {
        if ($user === null) {
            $session = $this->sessionCart();

            if ($quantity < 1) {
                unset($session[$product->getKey()]);
            } else {
                $session[$product->getKey()] = $quantity;
            }

            session()->put(self::SESSION_KEY, $session);

            return;
        }

        $cart = Cart::query()->firstOrCreate(['user_id' => $user->getKey()]);

        $cart->items()->updateOrCreate(
            ['product_id' => $product->getKey()],
            ['quantity' => $quantity],
        );
    }

    /**
     * Remove a product from the cart entirely.
     */
    public function remove(?User $user, Product $product): void
    {
        if ($user === null) {
            $session = $this->sessionCart();

            unset($session[$product->getKey()]);

            session()->put(self::SESSION_KEY, $session);

            return;
        }

        Cart::query()
            ->where('user_id', $user->getKey())
            ->first()
            ?->items()
            ->where('product_id', $product->getKey())
            ->delete();
    }

    /**
     * Empty the cart.
     */
    public function clear(?User $user): void
    {
        if ($user === null) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        Cart::query()
            ->where('user_id', $user->getKey())
            ->first()
            ?->items()
            ->delete();
    }

    /**
     * Describe why a single line cannot be checked out right now, if it can.
     *
     * @param  array{product: Product, quantity: int}  $line
     */
    public function problemFor(array $line): ?string
    {
        $product = $line['product'];

        if ($product->status !== ProductStatus::Active) {
            return "{$product->name} is no longer available.";
        }

        if ($product->stock_quantity < 1) {
            return "{$product->name} is out of stock.";
        }

        if ($line['quantity'] > $product->stock_quantity) {
            return "Only {$product->stock_quantity} of {$product->name} in stock.";
        }

        if ($line['quantity'] < 1) {
            return "The quantity for {$product->name} is invalid.";
        }

        return null;
    }

    /**
     * Collect every problem that blocks checkout for the given lines.
     *
     * @param  Collection<int, array{product: Product, quantity: int}>  $lines
     * @return list<string>
     */
    public function validateForCheckout(Collection $lines): array
    {
        return $lines
            ->map(fn (array $line): ?string => $this->problemFor($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Merge the guest session cart into the customer's database cart.
     *
     * Lines that are no longer purchasable are dropped, and quantities are
     * clamped to the current stock and per-item cap.
     */
    private function mergeSessionCartIntoDatabase(User $user): void
    {
        $session = $this->sessionCart();

        if ($session === []) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', array_keys($session))
            ->get()
            ->keyBy('id');

        $cart = Cart::query()->firstOrCreate(['user_id' => $user->getKey()]);

        foreach ($session as $productId => $quantity) {
            $product = $products->get($productId);

            if ($product === null || $product->status !== ProductStatus::Active) {
                continue;
            }

            $clamped = min((int) $quantity, $product->stock_quantity, self::MAX_PER_ITEM);

            if ($clamped < 1) {
                continue;
            }

            $item = $cart->items()->where('product_id', $productId)->first();

            if ($item !== null) {
                $item->update(['quantity' => min($item->quantity + $clamped, $product->stock_quantity, self::MAX_PER_ITEM)]);
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $clamped,
                ]);
            }
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * The combined quantity for an add, capped at stock and the per-item cap
     * but never lowered below what is already in the cart.
     */
    private function combinedQuantity(int $existing, int $quantity, Product $product): int
    {
        $cap = min($product->stock_quantity, self::MAX_PER_ITEM);

        if ($cap < 1) {
            return $existing;
        }

        return max($existing, min($existing + $quantity, $cap));
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int}>
     */
    private function sessionLines(): Collection
    {
        $session = $this->sessionCart();

        if ($session === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', array_keys($session))
            ->get()
            ->keyBy('id');

        return collect($session)
            ->map(fn ($quantity, $productId): ?array => isset($products[$productId])
                ? ['product' => $products[$productId], 'quantity' => (int) $quantity]
                : null)
            ->filter()
            ->values();
    }

    /**
     * Total quantity of a guest's session cart, ignoring lines whose
     * product no longer exists.
     */
    private function sessionCount(): int
    {
        $session = $this->sessionCart();

        if ($session === []) {
            return 0;
        }

        $available = Product::query()
            ->whereIn('id', array_keys($session))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $total = 0;

        foreach ($session as $productId => $quantity) {
            if (in_array((int) $productId, $available, true)) {
                $total += (int) $quantity;
            }
        }

        return $total;
    }

    /**
     * @return array<int, int>
     */
    private function sessionCart(): array
    {
        $cart = session()->get(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }
}
