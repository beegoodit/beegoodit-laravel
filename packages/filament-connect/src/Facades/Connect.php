<?php

namespace Beegoodit\FilamentConnect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $class)
 * @method static array getServices()
 * @method static string|null getService(string $name)
 *
 * @see \Beegoodit\FilamentConnect\Connect
 */
class Connect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Beegoodit\FilamentConnect\Connect::class;
    }
}
