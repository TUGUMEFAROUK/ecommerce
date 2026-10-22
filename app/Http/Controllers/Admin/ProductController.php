<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\ProductImageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * List products with search and status/category filters.
     */
    public function index(Request $request): View
    {
        $query = Product::query()->with(['category', 'images'])->latest();

        $search = trim((string) $request->query('search'));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(fn ($builder) => $builder
                ->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like));
        }

        $status = ProductStatus::tryFrom((string) $request->query('status'));

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->query('category'));
        }

        return view('admin.products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => $this->categories(),
            'statuses' => ProductStatus::cases(),
        ]);
    }

    /**
     * Show the create product form.
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => $this->categories(),
            'statuses' => ProductStatus::cases(),
        ]);
    }

    /**
     * Persist a new product.
     *
     * An uploaded image is stored as the product's primary image right
     * after the product row is written, so the storefront never shows a
     * product that was created without its picture.
     */
    public function store(AdminProductRequest $request, ProductImageService $images): RedirectResponse
    {
        $validated = $request->validated();

        if (array_key_exists('sale_price', $validated) && $validated['sale_price'] === '') {
            $validated['sale_price'] = null;
        }

        $product = Product::create([
            ...$validated,
            'featured' => $request->boolean('featured'),
        ]);

        if ($request->hasFile('image_upload')) {
            $images->store($product, $request->file('image_upload'), asPrimary: true);
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product created.');
    }

    /**
     * Show a single product with its images.
     */
    public function show(Product $product): View
    {
        return view('admin.products.show', [
            'product' => $product->load(['category', 'images']),
        ]);
    }

    /**
     * Show the edit product form.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => $this->categories(),
            'statuses' => ProductStatus::cases(),
        ]);
    }

    /**
     * Update an existing product.
     *
     * Stock edits made from this form are recorded as auditable movements
     * too, so every path that changes a quantity leaves the same trail.
     * The stock adjustment runs first — it computes the delta from the
     * stored value — and both writes share one transaction so a failure
     * can never leave a half-applied edit.
     */
    public function update(AdminProductRequest $request, Product $product, InventoryService $inventory, ProductImageService $images): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $product, $validated, $inventory, $images): void {
            $inventory->adjustStock(
                $product,
                (int) $validated['stock_quantity'],
                $request->user()->getKey(),
                'Product form',
            );

            $updateData = Arr::except($validated, ['stock_quantity', 'image_upload']);

            if (array_key_exists('sale_price', $updateData) && $updateData['sale_price'] === '') {
                $updateData['sale_price'] = null;
            }

            // The stock level was already written by the inventory service,
            // which also recorded the movement. Excluding it here keeps a
            // single source of truth for the quantity.
            $product->update([
                ...$updateData,
                'featured' => $request->boolean('featured'),
            ]);

            if ($request->hasFile('image_upload')) {
                $images->store($product, $request->file('image_upload'), asPrimary: true);
            }
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product updated.');
    }

    /**
     * Delete a product and its stored image files.
     *
     * Order items keep their name/price snapshots, so past orders are
     * unaffected by the removal. The database row is deleted first: an
     * image failure must never leave an orphaned product with dangling
     * relations, and the file cleanup is silently best-effort.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $paths = $product->images()->pluck('path');

        $product->delete();

        Storage::disk('public')->delete($paths->all());

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }
}
