<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront homepage.
     */
    public function index(): View
    {
        return view('home', [
            'featuredProducts' => Product::query()
                ->active()
                ->featured()
                ->with(['images', 'category'])
                ->latest()
                ->limit(8)
                ->get(),
            'newArrivals' => Product::query()
                ->active()
                ->with(['images', 'category'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
