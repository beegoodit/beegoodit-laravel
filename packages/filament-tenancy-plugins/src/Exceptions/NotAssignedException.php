<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Exceptions;

use RuntimeException;

class NotAssignedException extends RuntimeException
{
    public function __construct(public readonly string $plugin)
    {
        parent::__construct("Plugin [{$plugin}] is not assigned.");
    }
}
