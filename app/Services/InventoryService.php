<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Set a product's absolute stock level and record the signed delta as
     * an auditable movement.
     *
     * The caller never supplies a delta: it is computed from the stored
     * value so the recorded change always matches what actually happened.
     * No movement is written when the level is unchanged. Both writes
     * share one transaction.
     *
     * The actor is optional: a movement made by the system — such as the
     * stock returned when an order is cancelled — has no person behind it.
     */
    public function adjustStock(Product $product, int $newStock, ?int $actorUserId, ?string $reason = null): void
    {
        $previousStock = (int) $product->stock_quantity;

        if ($newStock === $previousStock) {
            return;
        }

        DB::transaction(function () use ($product, $newStock, $previousStock, $actorUserId, $reason): void {
            $product->update(['stock_quantity' => $newStock]);

            StockMovement::create([
                'product_id' => $product->getKey(),
                'user_id' => $actorUserId,
                'change' => $newStock - $previousStock,
                'stock_after' => $newStock,
                'reason' => $reason,
            ]);
        });
    }
}
