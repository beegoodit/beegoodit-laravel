<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tenant;

/**
 * Explicit no-scope resolver (always null).
 */
final class NullTenantResolver
{
    public function __invoke(): int|string|null
    {
        return null;
    }
}
