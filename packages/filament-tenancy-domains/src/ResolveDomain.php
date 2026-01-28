<?php

namespace BeegoodIT\FilamentTenancyDomains;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDomain
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
            app()->instance('resolvedDomain', $resolvedDomain);
            app()->instance('resolvedEntity', $resolvedDomain->model);
        }

        return $next($request);
    }
}
