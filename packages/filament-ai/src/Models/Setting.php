<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Models;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Laravel\Ai\Enums\Lab;

/**
 * @property int|null $id
 * @property bool $enabled
 * @property bool $mcp_enabled
 * @property Lab|null $provider
 * @property string|null $model
 * @property string|null $api_key
 * @property array<string, mixed>|null $capabilities
 * @property string|null $custom_instructions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    public const TABLE = 'beegoodit_filament_ai_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        $enabledColumn = $this->enabledColumn();

        $casts = [
            'mcp_enabled' => 'boolean',
            'provider' => Lab::class,
            'api_key' => 'encrypted',
            'capabilities' => 'array',
        ];

        if ($enabledColumn === 'enabled') {
            $casts['enabled'] = 'boolean';
        } else {
            $casts[$enabledColumn] = 'boolean';
        }

        return $casts;
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            FilamentAi::tenantColumn(),
            $this->enabledColumn(),
            'mcp_enabled',
            'provider',
            'model',
            'api_key',
            'capabilities',
            'custom_instructions',
        ];
    }

    /**
     * Public API is always `$settings->enabled`. Hosts may override {@see enabledColumn()}.
     */
    protected function enabled(): Attribute
    {
        $column = $this->enabledColumn();

        return Attribute::make(
            get: function (mixed $value) use ($column): bool {
                if ($column === 'enabled') {
                    return (bool) $value;
                }

                return (bool) ($this->attributes[$column] ?? false);
            },
            set: function (mixed $value) use ($column): array {
                return [$column => (bool) $value];
            },
        );
    }

    /**
     * DB column for the enabled flag. Override in host subclasses (e.g. TallCMS `is_enabled`).
     */
    protected function enabledColumn(): string
    {
        return 'enabled';
    }

    public static function forCurrentTenant(): ?static
    {
        $column = FilamentAi::tenantColumn();
        $tenantId = FilamentAi::tenantId();

        return static::query()
            ->when(
                $tenantId === null,
                fn ($query) => $query->whereNull($column),
                fn ($query) => $query->where($column, $tenantId),
            )
            ->first();
    }

    public function tenantKey(): int|string|null
    {
        return $this->getAttribute(FilamentAi::tenantColumn());
    }

    public function setTenantKey(int|string|null $value): static
    {
        $this->setAttribute(FilamentAi::tenantColumn(), $value);

        return $this;
    }

    public function hasConfiguredKey(): bool
    {
        return filled($this->api_key);
    }

    /**
     * Nested map: provider id → capability key → bool.
     *
     * @return array<string, array<string, bool>|mixed>
     */
    public function resolvedCapabilities(): array
    {
        return app(CapabilityRegistry::class)->merge(
            is_array($this->capabilities) ? $this->capabilities : null
        );
    }

    /**
     * Check a capability: can('provider', 'key') or can('provider.key').
     */
    public function can(string $providerOrCapability, ?string $capability = null): bool
    {
        $resolved = $this->resolvedCapabilities();
        $registry = app(CapabilityRegistry::class);

        if ($capability !== null) {
            return $registry->can($resolved, $providerOrCapability, $capability);
        }

        if (str_contains($providerOrCapability, '.')) {
            [$providerId, $key] = explode('.', $providerOrCapability, 2);

            return $registry->can($resolved, $providerId, $key);
        }

        return false;
    }

    public function customInstructionsFragment(): ?string
    {
        $instructions = trim((string) ($this->custom_instructions ?? ''));

        if ($instructions === '') {
            return null;
        }

        return <<<FRAGMENT
Tenant context (always apply — configured in AI Settings, not chat memory):
{$instructions}
FRAGMENT;
    }
}
