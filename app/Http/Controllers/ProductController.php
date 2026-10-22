<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * The number of products shown per catalog page.
     */
    private const PER_PAGE = 12;

    /**
     * The number of related and recently viewed products shown on a product page.
     */
    private const SUGGESTION_LIMIT = 4;

    /**
     * The number of product ids kept in the recently viewed history.
     */
    private const HISTORY_LIMIT = 8;

    /**
     * Session key for the products a visitor has recently viewed.
     *
     * Only product identifiers are stored, never customer details.
     */
    private const HISTORY_SESSION_KEY = 'recently_viewed_products';

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
     *
     * The related rail and the recently viewed rail are both resolved from
     * the database with their images and categories eager loaded, so the
     * page costs a fixed number of queries regardless of how many products
     * are suggested.
     */
    public function show(Request $request, Product $product): View
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load(['images', 'category.parent']);

        $recentlyViewed = $this->recentlyViewed($request, $product);

        $this->rememberRecentlyViewed($request, $product);

        return view('product', [
            'product' => $product,
            'relatedProducts' => $this->relatedProducts($product),
            'recentlyViewed' => $recentlyViewed,
        ]);
    }

    /**
     * Active products from the same category, preferring featured and
     * well-stocked items.
     *
     * @return Collection<int, Product>
     */
    private function relatedProducts(Product $product): Collection
    {
        return Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->with(['images', 'category'])
            ->orderByDesc('featured')
            ->orderByDesc('stock_quantity')
            ->orderByDesc('id')
            ->limit(self::SUGGESTION_LIMIT)
            ->get();
    }

    /**
     * The most recently viewed active products, excluding the current one.
     *
     * @return Collection<int, Product>
     */
    private function recentlyViewed(Request $request, Product $product): Collection
    {
        $ids = $this->recentlyViewedIds($request)
            ->reject(fn (int $id): bool => $id === (int) $product->getKey())
            ->take(self::SUGGESTION_LIMIT)
            ->values();

        if ($ids->isEmpty()) {
            return new Collection;
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', $ids->all())
            ->with(['images', 'category'])
            ->get()
            ->keyBy('id');

        return new Collection(
            $ids
                ->map(fn (int $id): ?Product => $products->get($id))
                ->filter()
                ->values()
                ->all()
        );
    }

    /**
     * Push the current product to the front of the visitor's history.
     */
    private function rememberRecentlyViewed(Request $request, Product $product): void
    {
        $ids = $this->recentlyViewedIds($request)
            ->reject(fn (int $id): bool => $id === (int) $product->getKey())
            ->prepend((int) $product->getKey())
            ->take(self::HISTORY_LIMIT)
            ->values()
            ->all();

        $request->session()->put(self::HISTORY_SESSION_KEY, $ids);
    }

    /**
     * The product identifiers stored in the visitor's history.
     *
     * @return BaseCollection<int, int>
     */
    private function recentlyViewedIds(Request $request): BaseCollection
    {
        $stored = $request->session()->get(self::HISTORY_SESSION_KEY, []);

        if (! is_array($stored)) {
            return collect();
        }

        return collect($stored)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
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
