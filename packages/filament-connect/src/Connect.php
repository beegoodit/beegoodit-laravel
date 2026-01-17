<?php

namespace Beegoodit\FilamentConnect;

class Connect
{
    protected static array $services = [];

    public static function register(string $name, string $class): void
    {
        static::$services[$name] = $class;
    }

    public static function getServices(): array
    {
        return static::$services;
    }

    public static function getService(string $name): ?string
    {
        return static::$services[$name] ?? null;
    }
}
