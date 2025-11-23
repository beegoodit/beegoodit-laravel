<?php

namespace BeeGoodIT\FilamentI18n\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($user = $request->user()) {
            // Check if user has the getLocale method (from HasI18nPreferences trait)
            if (method_exists($user, 'getLocale')) {
                App::setLocale($user->getLocale());
            } elseif (isset($user->locale)) {
                App::setLocale($user->locale);
            }
        }

        return $next($request);
    }
}
