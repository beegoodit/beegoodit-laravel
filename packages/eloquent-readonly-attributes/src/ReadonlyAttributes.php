<?php

namespace BeegoodIT\EloquentReadonlyAttributes;

use Throwable;

class ReadonlyAttributes
{
    private static int $guardBypassDepth = 0;

    /**
     * Execute the callback while readonly attribute guards are disabled.
     *
     * @template TValue
     *
     * @param  callable(): TValue  $callback
     * @return TValue
     *
     * @throws Throwable
     */
    public static function withoutGuards(callable $callback): mixed
    {
        self::$guardBypassDepth++;

        try {
            return $callback();
        } finally {
            self::$guardBypassDepth--;
        }
    }

    public static function guardsAreBypassed(): bool
    {
        return self::$guardBypassDepth > 0;
    }
}
