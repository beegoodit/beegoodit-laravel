<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Explicit no-scope resolver (always null).
 */
final class NullTenantResolver
{
    public function __invoke(): ?Model
    {
        return null;
    }
}
