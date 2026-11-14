<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use BeegoodIT\FilamentAi\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class FilamentAi
{
    /** @var class-string<Setting>|null */
    private static ?string $settingsModel = null;

    /**
     * Hosts (e.g. TallCMS) may swap the settings Eloquent model.
     *
     * @param  class-string<Setting>|null  $class  null resets to {@see Setting}
     */
    public static function useSettingsModel(?string $class): void
    {
        self::$settingsModel = $class;
    }

    /**
     * @return class-string<Setting>
     */
    public static function settingsModel(): string
    {
        return self::$settingsModel ?? Setting::class;
    }

    /**
     * @return Builder<Setting>
     */
    public static function settings(): Builder
    {
        $class = self::settingsModel();

        return $class::query();
    }

    public static function settingsTable(): string
    {
        return (new (self::settingsModel()))->getTable();
    }

    /**
     * Current owner from `filament-ai.tenant_resolver`.
     *
     * null / empty config → no scope. The resolver returns a model or null.
     */
    public static function subject(): ?Model
    {
        $resolved = self::resolveConfiguredSubject();

        return $resolved instanceof Model ? $resolved : null;
    }

    /**
     * Native key of the current subject. Null when AI is not scoped.
     *
     * CMS soft-deps still read this (and `siteId`) to filter their own columns.
     */
    public static function tenantId(): int|string|null
    {
        return self::subject()?->getKey();
    }

    private static function resolveConfiguredSubject(): mixed
    {
        $resolver = config('filament-ai.tenant_resolver');

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

    public static function tenantColumn(): string
    {
        $column = config('filament-ai.tenant_column', 'tenant_id');

        return is_string($column) && $column !== '' ? $column : 'tenant_id';
    }

    public static function tenantKeyType(): string
    {
        $type = config('filament-ai.tenant_key_type', 'int');

        return $type === 'uuid' ? 'uuid' : 'int';
    }

    public static function userKeyType(): string
    {
        $type = config('filament-ai.user_key_type', 'int');

        return $type === 'uuid' ? 'uuid' : 'int';
    }
}
