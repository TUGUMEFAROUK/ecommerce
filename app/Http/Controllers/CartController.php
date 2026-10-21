<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\CartPricingService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the current cart with server-computed totals.
     */
    public function index(Request $request, CartService $carts, CartPricingService $pricing): View
    {
        $lines = $carts->lines($request->user());

        return view('cart.index', [
            'lines' => $lines,
            'quote' => $pricing->quote($lines),
            'lineTotals' => $lines
                ->mapWithKeys(fn (array $line): array => [
                    $line['product']->getKey() => $pricing->lineTotal($line['product']->price, $line['quantity']),
                ])
                ->all(),
            'problems' => $lines
                ->mapWithKeys(fn (array $line): array => [$line['product']->getKey() => $carts->problemFor($line)])
                ->all(),
        ]);
    }

    /**
     * Add a product to the guest session cart or the customer's cart.
     */
    public function store(Request $request, Product $product, CartService $carts): RedirectResponse
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        if (! $product->inStock()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is out of stock.',
            ]);
        }

        $max = min($product->stock_quantity, CartService::MAX_PER_ITEM);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ]);

        $carts->add($request->user(), $product, (int) $validated['quantity']);

        return back()->with('status', 'Added to cart.');
    }

    /**
     * Set the quantity for a cart line.
     */
    public function update(Request $request, Product $product, CartService $carts): RedirectResponse
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        if (! $product->inStock()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is out of stock.',
            ]);
        }

        $max = min($product->stock_quantity, CartService::MAX_PER_ITEM);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ]);

        $carts->updateQuantity($request->user(), $product, (int) $validated['quantity']);

        return back()->with('status', 'Quantity updated.');
    }

    /**
     * Remove a single product from the cart.
     */
    public function destroy(Request $request, Product $product, CartService $carts): RedirectResponse
    {
        $carts->remove($request->user(), $product);

        return back()->with('status', 'Item removed from cart.');
    }

    /**
     * Empty the cart.
     */
    public function clear(Request $request, CartService $carts): RedirectResponse
    {
        $carts->clear($request->user());

        return redirect()->route('cart.index')->with('status', 'Cart cleared.');
    }
}
