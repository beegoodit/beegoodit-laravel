<?php

namespace BeegoodIT\FilamentTenancyDomains\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDomainContextMiddleware
{
    /**
     * Handle the request.
     *
     * @param  string  $requirement  The domain type (e.g. 'platform') OR the model class (e.g. 'App\Models\Team')
     */
    public function handle(Request $request, Closure $next, string $requirement): Response
    {
        $domain = app()->bound('resolvedDomain') ? app('resolvedDomain') : null;

        // Implementation Detail: Local development and Tests often use hosts like 'localhost'
        // that might not be in the 'domains' table. We treat these as 'platform'.
        if ($requirement === 'platform' && ! $domain) {
            $currentHost = $request->getHost();
            $platformDomain = config('filament-tenancy-domains.platform_domain');

            if ($currentHost === 'localhost' || $currentHost === '127.0.0.1' || $currentHost === $platformDomain) {
                return $next($request);
            }
        }

        if (! $domain) {
            abort(404);
        }

        // Match by domain type (from the 'type' column in the domains table)
        if ($domain->type === $requirement) {
            return $next($request);
        }

        // Match by model class (from the 'model_type' column in the domains table)
        if ($domain->model_type === $requirement) {
            return $next($request);
        }

        abort(404);
    }
}
