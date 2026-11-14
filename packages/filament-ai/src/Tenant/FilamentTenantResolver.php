<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tenant;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolve tenant id from the current Filament panel tenant.
 */
final class FilamentTenantResolver
{
    public function __invoke(): ?Model
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Model ? $tenant : null;
    }
}
