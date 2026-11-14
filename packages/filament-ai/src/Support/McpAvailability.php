<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Laravel\Ai\Contracts\Tool;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Server;

/**
 * Soft-detect host Composer peer laravel/mcp. Never hard-fail boot when missing.
 */
final class McpAvailability
{
    public static function packagePresent(): bool
    {
        return class_exists(Server::class)
            && class_exists(Mcp::class);
    }

    public static function aiPackagePresent(): bool
    {
        return interface_exists(Tool::class);
    }
}
