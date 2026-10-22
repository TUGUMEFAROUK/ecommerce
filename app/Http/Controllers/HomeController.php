<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\SettingsService;
use App\Services\StoreImageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront homepage.
     *
     * Selection rules for the three product rails:
     *
     * - Featured: products an administrator has explicitly flagged.
     * - New arrivals: the most recently added active products.
     * - Bestsellers: active products ranked by the number of order lines
     *   they have sold, with the most recent product breaking a tie.
     *
     * Every rail reads from the database, so the homepage never shows a
     * product, price or category that the store does not actually sell.
     */
    public function index(SettingsService $settings, StoreImageService $images): View
    {
        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with(['images', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $newArrivals = Product::query()
            ->active()
            ->with(['images', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $bestSellers = Product::query()
            ->active()
            ->with(['images', 'category'])
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $artwork = $this->catalogueArtwork($images, $newArrivals, $featuredProducts, $bestSellers);

        return view('home', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->withCount(['products' => fn ($query) => $query->active()])
                ->with(['products' => fn ($query) => $query->active()->with('images')->orderBy('id')])
                ->orderBy('name')
                ->get(),
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'heroImage' => $images->url($settings->get('hero_image') ?: null) ?? ($artwork[0] ?? null),
            'promoImage' => $images->url($settings->get('promo_image') ?: null) ?? ($artwork[1] ?? $artwork[0] ?? null),
            'newsletterImage' => $images->url($settings->get('newsletter_image') ?: null) ?? ($artwork[2] ?? $artwork[0] ?? null),
        ]);
    }

    /**
     * Collect distinct catalogue photography for the editorial bands.
     *
     * The bands fall back to real product photography so the homepage is
     * never filled with placeholder art while the store has images to
     * show. Each band takes a different image where one is available.
     *
     * @param  Collection<int, Product>  ...$rails
     * @return list<string>
     */
    private function catalogueArtwork(StoreImageService $images, Collection ...$rails): array
    {
        $urls = [];

        foreach ($rails as $rail) {
            foreach ($rail as $product) {
                $url = $images->url($product->images->first()?->path);

                if ($url !== null && ! in_array($url, $urls, true)) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }
}
