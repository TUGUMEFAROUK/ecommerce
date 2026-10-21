<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Trust X-Forwarded-* headers only from proxies the operator has listed
 * in the TRUSTED_PROXIES environment variable.
 *
 * The proxy list is read per request rather than in bootstrap/app.php's
 * middleware callback, because that callback runs before the config
 * repository exists (the console kernel resolves HttpKernel that early).
 */
class TrustProxies extends \Illuminate\Http\Middleware\TrustProxies
{
    public function handle(Request $request, Closure $next): mixed
    {
        $configured = (string) config('app.trusted_proxies', '');

        $this->proxies = match ($configured) {
            '' => null,
            '*' => '*',
            default => array_values(array_filter(array_map('trim', explode(',', $configured)))),
        };

        return parent::handle($request, $next);
    }
}
