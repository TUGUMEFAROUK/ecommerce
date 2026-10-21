<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Store an uploaded image for a product.
     *
     * The first uploaded image becomes the primary image; files land on the
     * public disk under server-generated hashed names.
     */
    public function store(AdminProductImageRequest $request, Product $product): RedirectResponse
    {
        $path = $request->file('image')->store('products', 'public');

        $hasImages = $product->images()->exists();

        $product->images()->create([
            'path' => $path,
            'position' => $hasImages ? (int) $product->images()->max('position') + 1 : 0,
            'is_primary' => ! $hasImages,
        ]);

        return back()->with('status', 'Image uploaded.');
    }

    /**
     * Replace an existing image file, keeping its record and position.
     *
     * The record points at the new file first; only then is the old file
     * removed, so a cleanup failure can never leave the row referencing a
     * deleted path.
     */
    public function replace(AdminProductImageRequest $request, ProductImage $image): RedirectResponse
    {
        $oldPath = $image->path;

        $newPath = $request->file('image')->store('products', 'public');

        $image->update(['path' => $newPath]);

        Storage::disk('public')->delete($oldPath);

        return back()->with('status', 'Image replaced.');
    }

    /**
     * Delete an image file and record, promoting the next image when the
     * primary one is removed.
     *
     * The route middleware already restricts this to admins; the explicit
     * check keeps the action safe even if the route grouping ever changes.
     */
    public function destroy(Request $request, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $product = $image->product;
        $wasPrimary = $image->is_primary;

        $image->delete();

        Storage::disk('public')->delete($image->path);

        if ($wasPrimary && $product->images()->exists()) {
            $product->images()->orderBy('position')->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', 'Image removed.');
    }
}
