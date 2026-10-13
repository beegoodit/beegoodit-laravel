<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Workspace;

/**
 * Explicit no-scope resolver (always null).
 */
final class NullWorkspaceResolver
{
    public function __invoke(): int|string|null
    {
        return null;
    }
}
