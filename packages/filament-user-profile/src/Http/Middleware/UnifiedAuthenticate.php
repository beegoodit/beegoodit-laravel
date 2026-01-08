<?php

namespace BeeGoodIT\FilamentUserProfile\Http\Middleware;

use Filament\Http\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class UnifiedAuthenticate extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function unauthenticated($request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request)
        );
    }

    protected function redirectTo($request): ?string
    {
        return route('filament.me.auth.login');
    }
}
