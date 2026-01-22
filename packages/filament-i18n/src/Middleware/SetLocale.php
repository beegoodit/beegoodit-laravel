<?php

namespace BeegoodIT\FilamentI18n\Middleware;

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
        $locale = $request->segment(1);

        $availableLocales = config('filament-i18n.available_locales', ['en', 'de']);

        if (!in_array($locale, $availableLocales)) {
            $user = $request->user();

            if ($user && method_exists($user, 'getLocale')) {
                // Prioritize user preference if logged in
                $locale = $user->getLocale();
            } elseif ($user && isset($user->locale)) {
                $locale = $user->locale;
            } elseif (session()->has('locale')) {
                // Fallback to session
                $locale = session('locale');
            } else {
                // Fallback to config
                $locale = config('app.locale');
            }
        } else {
            // Update session if URL has valid locale
            session(['locale' => $locale]);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
