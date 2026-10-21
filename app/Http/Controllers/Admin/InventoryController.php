<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * List active products at or below the low-stock threshold.
     */
    public function index(Request $request, SettingsService $settings): View
    {
        $threshold = $settings->lowStockThreshold();

        return view('admin.inventory.index', [
            'products' => Product::query()
                ->where('status', ProductStatus::Active)
                ->where('stock_quantity', '<=', $threshold)
                ->orderBy('stock_quantity')
                ->paginate(10)
                ->withQueryString(),
            'threshold' => $threshold,
            'movements' => StockMovement::query()
                ->with(['product', 'user'])
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Set the absolute stock level for a product.
     *
     * The client never sends a delta: InventoryService computes the signed
     * change from the stored value and records it as an auditable movement.
     */
    public function update(
        StockAdjustmentRequest $request,
        Product $product,
        InventoryService $inventory,
    ): RedirectResponse {
        $validated = $request->validated();

        $inventory->adjustStock(
            $product,
            (int) $validated['stock_quantity'],
            $request->user()->getKey(),
            $validated['reason'] ?? null,
        );

        return back()->with('status', 'Stock updated.');
    }
}
