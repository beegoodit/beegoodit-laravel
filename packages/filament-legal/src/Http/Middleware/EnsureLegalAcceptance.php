<?php

namespace BeegoodIT\FilamentLegal\Http\Middleware;

use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use BeegoodIT\FilamentLegal\Models\PolicyAcceptance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegalAcceptance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Only enforce for authenticated users
        if (! $user) {
            return $next($request);
        }

        // Get the current active privacy policy
        $activePrivacyPolicy = LegalPolicy::getActive('privacy');

        if (! $activePrivacyPolicy instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy) {
            return $next($request);
        }

        // Check if the user has accepted this specific version
        $hasAccepted = method_exists($user, 'hasAcceptedLatestPolicy')
            ? $user->hasAcceptedLatestPolicy('privacy')
            : PolicyAcceptance::where('user_id', $user->id)
                ->where('legal_policy_id', $activePrivacyPolicy->id)
                ->exists();

        // If not accepted, redirect to acceptance page (unless already there or on an allowed route)
        if (! $hasAccepted && ! $this->shouldSkip($request)) {
            if ($request->isMethod('GET')) {
                session()->put('url.intended', $request->fullUrl());
            }

            return to_route('filament-legal.acceptance');
        }

        return $next($request);
    }

    /**
     * Determine if the legal gate should be skipped for the current request.
     */
    protected function shouldSkip(Request $request): bool
    {
        $allowedRoutes = [
            'filament-legal.acceptance',
            'filament-legal.submit-acceptance',
            'logout',
        ];

        return $request->routeIs($allowedRoutes);
    }
}
