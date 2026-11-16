<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Models;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Support\SubjectScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Ai\Enums\Lab;

/**
 * @property int|null $id
 * @property bool $is_enabled
 * @property bool $is_mcp_enabled
 * @property bool $enabled  Public alias via {@see enabled()}
 * @property bool $mcp_enabled  Public alias via {@see mcpEnabled()}
 * @property Lab|null $provider
 * @property string|null $model
 * @property string|null $api_key
 * @property array<string, mixed>|null $capabilities
 * @property string|null $custom_instructions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    public const TABLE = 'beegoodit_filament_ai_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            $this->enabledColumn() => 'boolean',
            $this->mcpEnabledColumn() => 'boolean',
            'provider' => Lab::class,
            'api_key' => 'encrypted',
            'capabilities' => 'array',
        ];
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
            'subject_type',
            'subject_id',
            'enabled',
            'mcp_enabled',
            $this->enabledColumn(),
            $this->mcpEnabledColumn(),
            'provider',
            'model',
            'api_key',
            'capabilities',
            'custom_instructions',
        ];
    }

    /**
     * Public API is `$settings->enabled`. DB column via {@see enabledColumn()}.
     */
    protected function enabled(): Attribute
    {
        $column = $this->enabledColumn();

        return Attribute::make(
            get: fn (): bool => (bool) ($this->attributes[$column] ?? false),
            set: fn (mixed $value): array => [$column => (bool) $value],
        );
    }

    /**
     * Public API is `$settings->mcp_enabled`. DB column via {@see mcpEnabledColumn()}.
     */
    protected function mcpEnabled(): Attribute
    {
        $column = $this->mcpEnabledColumn();

        return Attribute::make(
            get: fn (): bool => (bool) ($this->attributes[$column] ?? false),
            set: fn (mixed $value): array => [$column => (bool) $value],
        );
    }

    /**
     * DB column for the AI-enabled flag.
     */
    protected function enabledColumn(): string
    {
        return 'is_enabled';
    }

    /**
     * DB column for remote MCP enabled.
     */
    protected function mcpEnabledColumn(): string
    {
        return 'is_mcp_enabled';
    }

    public static function forCurrentTenant(): ?static
    {
        return SubjectScope::apply(static::query(), FilamentAi::subject())->first();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForSubject(Builder $query, ?Model $subject): Builder
    {
        return SubjectScope::apply($query, $subject);
    }

    public function assignSubject(?Model $subject): static
    {
        $this->forceFill(SubjectScope::attributes($subject));

        return $this;
    }

    public function subjectKey(): ?string
    {
        $id = $this->getAttribute('subject_id');

        return $id === null || $id === '' ? null : (string) $id;
    }

    public function tenantKey(): int|string|null
    {
        return $this->subjectKey();
    }

    public function setTenantKey(int|string|null $value): static
    {
        $this->setAttribute('subject_id', $value === null ? null : (string) $value);

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
