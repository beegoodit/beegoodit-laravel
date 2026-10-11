<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins;

use BeegoodIT\FilamentTenancyPlugins\Exceptions\NotAssignedException;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class Tenant
{
    /**
     * @var array<string, PluginDefinition>
     */
    private array $definitions = [];

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function register(string $id, string $label, array $meta = []): void
    {
        $catalog = app(self::class);

        if (isset($catalog->definitions[$id])) {
            if ($catalog->definitions[$id]->label === $label) {
                return;
            }

            throw new InvalidArgumentException(
                "Plugin [{$id}] is already registered as [{$catalog->definitions[$id]->label}].",
            );
        }

        $catalog->definitions[$id] = new PluginDefinition($id, $label, $meta);
    }

    /**
     * @return list<PluginDefinition>
     */
    public static function all(): array
    {
        return array_values(app(self::class)->definitions);
    }

    public static function hasDefinition(string $id): bool
    {
        return isset(app(self::class)->definitions[$id]);
    }

    public static function for(Model $tenant): Access
    {
        return new Access($tenant);
    }

    public static function allows(?Model $tenant, string $id): bool
    {
        if (! $tenant instanceof Model) {
            return false;
        }

        return self::for($tenant)->has($id);
    }

    public static function has(string $id): bool
    {
        return self::allows(self::current(), $id);
    }

    public static function find(string $id): ?Model
    {
        $class = config('filament-tenancy-plugins.tenant_model');

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            return null;
        }

        $slugAttribute = config('filament-tenancy-plugins.slug_attribute', 'slug');

        $tenant = $class::query()->where($slugAttribute, $id)->first();

        if ($tenant instanceof Model) {
            return $tenant;
        }

        if (! self::looksLikeKey($class, $id)) {
            return null;
        }

        $tenant = $class::query()->find($id);

        return $tenant instanceof Model ? $tenant : null;
    }

    /**
     * @param  class-string<Model>  $class
     */
    private static function looksLikeKey(string $class, string $id): bool
    {
        $model = new $class;

        return match ($model->getKeyType()) {
            'int' => ctype_digit($id),
            default => Str::isUuid($id) || Str::isUlid($id),
        };
    }

    public static function ensure(Model|string $tenant, string $id): void
    {
        $resolved = $tenant instanceof Model ? $tenant : self::find($tenant);

        if (! self::allows($resolved, $id)) {
            throw new NotAssignedException($id);
        }
    }

    private static function current(): ?Model
    {
        if (! class_exists(Filament::class)) {
            return null;
        }

        try {
            $tenant = Filament::getTenant();
        } catch (Throwable) {
            return null;
        }

        return $tenant instanceof Model ? $tenant : null;
    }
}
