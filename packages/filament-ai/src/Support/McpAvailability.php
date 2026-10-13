<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

/**
 * Soft-detect host Composer peer laravel/mcp. Never hard-fail boot when missing.
 */
final class McpAvailability
{
    public static function packagePresent(): bool
    {
        return class_exists(\Laravel\Mcp\Server::class)
            && class_exists(\Laravel\Mcp\Facades\Mcp::class);
    }

    public static function aiPackagePresent(): bool
    {
        return interface_exists(\Laravel\Ai\Contracts\Tool::class);
    }
}
