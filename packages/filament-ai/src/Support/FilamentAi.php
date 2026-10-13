<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

final class FilamentAi
{
    /**
     * Current workspace id from config `filament-ai.workspace_resolver`.
     *
     * null / empty config → no scope. Class-string of an invokable, or a callable.
     */
    public static function workspaceId(): int|string|null
    {
        $resolver = config('filament-ai.workspace_resolver');

        if ($resolver === null || $resolver === false || $resolver === '') {
            return null;
        }

        if (is_string($resolver)) {
            if (class_exists($resolver) || app()->bound($resolver)) {
                $instance = app($resolver);

                if (is_callable($instance)) {
                    return $instance();
                }
            }

            return null;
        }

        if (is_callable($resolver)) {
            return $resolver();
        }

        return null;
    }

    public static function workspaceColumn(): string
    {
        $column = config('filament-ai.workspace_column', 'workspace_id');

        return is_string($column) && $column !== '' ? $column : 'workspace_id';
    }

    public static function workspaceKeyType(): string
    {
        $type = config('filament-ai.workspace_key_type', 'int');

        return $type === 'uuid' ? 'uuid' : 'int';
    }
}
