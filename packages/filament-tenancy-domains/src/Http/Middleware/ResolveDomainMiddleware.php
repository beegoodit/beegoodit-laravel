<?php

namespace BeegoodIT\FilamentTenancyDomains\Http\Middleware;

use BeegoodIT\FilamentTenancyDomains\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDomainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        $resolvedDomain = Domain::where('domain', $host)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNotNull('verified_at')->orWhere('type', 'platform'))
            ->with('model')
            ->first();

        if ($resolvedDomain && $resolvedDomain->model) {
            app()->instance("resolvedDomain", $resolvedDomain);
            app()->instance("resolvedEntity", $resolvedDomain->model);
        }

        return $next($request);
    }
}
