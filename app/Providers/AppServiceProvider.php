<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ViewFactory::composer('components.partials.site-header', function (View $view): void {
            $view->with('categories', Category::query()->whereNull('parent_id')->orderBy('name')->get());
            $view->with('cartItemCount', app(CartService::class)->count(auth()->user()));
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = $request->input('email');
            $key = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('cart', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->user()?->getKey() ?? 'guest:'.$request->ip());
        });

        RateLimiter::for('payments-webhook', function (): Limit {
            return Limit::perMinute(60)->by('payments-webhook');
        });
    }
}
