<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Webhook deliveries are authenticated by their verif-hash
        // signature, not by a session, so they carry no CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        // Only trust X-Forwarded-* headers from proxies the operator has
        // explicitly listed in TRUSTED_PROXIES; trusting them blindly lets
        // any client spoof its own IP address for rate limiting and logs.
        $middleware->prepend(TrustProxies::class);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/account');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
