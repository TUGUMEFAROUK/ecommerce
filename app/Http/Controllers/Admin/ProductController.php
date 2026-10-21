<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
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
        $query = Product::query()->with('category')->latest();

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
     */
    public function store(AdminProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            ...$request->validated(),
            'featured' => $request->boolean('featured'),
        ]);

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
            'product' => $product,
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
    public function update(AdminProductRequest $request, Product $product, InventoryService $inventory): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $product, $validated, $inventory): void {
            $inventory->adjustStock(
                $product,
                (int) $validated['stock_quantity'],
                $request->user()->getKey(),
                'Product form',
            );

            // The stock level was already written by the inventory service,
            // which also recorded the movement. Excluding it here keeps a
            // single source of truth for the quantity.
            $product->update([
                ...Arr::except($validated, ['stock_quantity']),
                'featured' => $request->boolean('featured'),
            ]);
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
