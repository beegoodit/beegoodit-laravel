<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Workspace;

use Filament\Facades\Filament;

/**
 * Resolve workspace id from the current Filament panel tenant.
 */
final class FilamentTenantWorkspaceResolver
{
    public function __invoke(): int|string|null
    {
        return Filament::getTenant()?->getKey();
    }
}
