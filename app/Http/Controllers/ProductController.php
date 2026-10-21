<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * The number of products shown per catalog page.
     */
    private const PER_PAGE = 12;

    /**
     * The whitelisted catalog sort orders.
     *
     * @var list<string>
     */
    private const SORTS = ['newest', 'price_asc', 'price_desc'];

    /**
     * The whitelisted stock availability filters.
     *
     * @var list<string>
     */
    private const AVAILABILITIES = ['all', 'in_stock', 'out_of_stock'];

    /**
     * Display the product catalog with search, filtering, and sorting.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100', 'exists:categories,slug'],
            'sort' => ['nullable', 'string', Rule::in(self::SORTS)],
            'availability' => ['nullable', 'string', Rule::in(self::AVAILABILITIES)],
        ]);

        $products = $this->catalogQuery($filters)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $currentCategory = null;

        if (filled($filters['category'] ?? null)) {
            $currentCategory = Category::query()->where('slug', $filters['category'])->first();
        }

        return view('catalog', [
            'products' => $products,
            'currentCategory' => $currentCategory,
            'catalogCategories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Display a single product in the storefront.
     */
    public function show(Product $product): View
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load(['images', 'category.parent']);

        return view('product', [
            'product' => $product,
        ]);
    }

    /**
     * Build the filtered and sorted catalog query from validated filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    private function catalogQuery(array $filters)
    {
        $query = Product::query()->active()->with(['images', 'category']);

        if (filled($filters['search'] ?? null)) {
            $term = '%'.addcslashes($filters['search'], '%_\\').'%';

            $query->where(fn ($inner) => $inner
                ->where('name', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('sku', 'like', $term));
        }

        if (filled($filters['category'] ?? null)) {
            $slug = $filters['category'];

            $query->whereHas('category', fn ($inner) => $inner
                ->where('slug', $slug)
                ->orWhereHas('parent', fn ($parent) => $parent->where('slug', $slug)));
        }

        match ($filters['sort'] ?? null) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        match ($filters['availability'] ?? null) {
            'in_stock' => $query->where('stock_quantity', '>', 0),
            'out_of_stock' => $query->where('stock_quantity', 0),
            default => null,
        };

        return $query;
    }
}
