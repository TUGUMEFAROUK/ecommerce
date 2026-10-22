<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $images) {}

    /**
     * Store an uploaded image for a product.
     *
     * The first uploaded image becomes the primary image; files land on the
     * public disk under server-generated hashed names.
     */
    public function store(AdminProductImageRequest $request, Product $product): RedirectResponse
    {
        $this->images->store($product, $request->file('image'));

        return back()->with('status', 'Image uploaded.');
    }

    /**
     * Replace an existing image file, keeping its record and position.
     */
    public function replace(AdminProductImageRequest $request, ProductImage $image): RedirectResponse
    {
        $this->images->replace($image, $request->file('image'));

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

        $this->images->delete($image);

        return back()->with('status', 'Image removed.');
    }
}
