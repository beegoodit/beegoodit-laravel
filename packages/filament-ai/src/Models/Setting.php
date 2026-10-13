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
        return (string) config('filament-ai.settings_table', 'beegoodit_filament_ai_settings');
    }

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            FilamentAi::workspaceColumn(),
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
     * Hosts may store the flag as `is_enabled` (TallCMS) while API stays `enabled`.
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

    protected function enabledColumn(): string
    {
        $column = config('filament-ai.enabled_column', 'enabled');

        return is_string($column) && $column !== '' ? $column : 'enabled';
    }

    public static function forCurrentWorkspace(): ?self
    {
        $column = FilamentAi::workspaceColumn();
        $workspaceId = FilamentAi::workspaceId();

        return static::query()
            ->when(
                $workspaceId === null,
                fn ($query) => $query->whereNull($column),
                fn ($query) => $query->where($column, $workspaceId),
            )
            ->first();
    }

    public function workspaceKey(): int|string|null
    {
        return $this->getAttribute(FilamentAi::workspaceColumn());
    }

    public function setWorkspaceKey(int|string|null $value): static
    {
        $this->setAttribute(FilamentAi::workspaceColumn(), $value);

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
Workspace context (always apply — configured in AI Settings, not chat memory):
{$instructions}
FRAGMENT;
    }
}
