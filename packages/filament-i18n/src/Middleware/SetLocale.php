<?php

namespace BeegoodIT\FilamentI18n\Middleware;

use BeegoodIT\FilamentI18n\Facades\FilamentI18n;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->segment(1);

        if (! in_array($locale, FilamentI18n::availableLocales())) {
            $user = $request->user() ?? Auth::user();
            if ($user && ! empty($user->locale)) {
                // Prioritize user preference if logged in
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
