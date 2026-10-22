<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductImageService
{
    /**
     * Store an uploaded image for a product.
     *
     * The first image of a product becomes its primary image. A file
     * uploaded as an explicit primary demotes the previous primary instead
     * of leaving the product with two primary images.
     */
    public function store(Product $product, UploadedFile $file, bool $asPrimary = false): ProductImage
    {
        $path = $this->storeUpload($file);

        $hasImages = $product->images()->exists();

        if ($asPrimary && $hasImages) {
            $product->images()->where('is_primary', true)->update(['is_primary' => false]);
        }

        return $product->images()->create([
            'path' => $path,
            'position' => $hasImages ? (int) $product->images()->max('position') + 1 : 0,
            'is_primary' => $asPrimary || ! $hasImages,
        ]);
    }

    /**
     * Replace the file behind an existing image, keeping its position.
     *
     * The record points at the new file first; only then is the old file
     * removed, so a cleanup failure can never leave the row referencing a
     * deleted path.
     */
    public function replace(ProductImage $image, UploadedFile $file): ProductImage
    {
        $oldPath = $image->path;

        $newPath = $this->storeUpload($file);

        $image->update(['path' => $newPath]);

        Storage::disk('public')->delete($oldPath);

        return $image;
    }

    /**
     * Delete an image file and record, promoting the next image when the
     * primary one is removed.
     */
    public function delete(ProductImage $image): void
    {
        $product = $image->product;
        $wasPrimary = $image->is_primary;

        $image->delete();

        Storage::disk('public')->delete($image->path);

        if ($wasPrimary && $product->images()->exists()) {
            $product->images()->orderBy('position')->first()?->update(['is_primary' => true]);
        }
    }

    /**
     * Write the uploaded file to the public disk.
     *
     * `store()` reports a failure by returning false rather than throwing,
     * so the result is checked before it is written to the database: a
     * record must never point at a file that was never saved.
     *
     * @throws ValidationException
     */
    private function storeUpload(UploadedFile $file): string
    {
        $path = $file->store('products', 'public');

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'image' => 'The image could not be saved. Please try again.',
            ]);
        }

        return $path;
    }
}
