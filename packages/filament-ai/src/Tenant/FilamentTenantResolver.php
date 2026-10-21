<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tenant;

use Filament\Facades\Filament;

/**
 * Resolve tenant id from the current Filament panel tenant.
 */
final class FilamentTenantResolver
{
    public function __invoke(): int|string|null
    {
        return Filament::getTenant()?->getKey();
    }
}
