<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Concerns;

use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Database\Eloquent\Builder;

/**
 * Minimal tool helpers for host capability providers (sync-compatible surface).
 */
trait InteractsWithTools
{
    protected function context(): ToolContext
    {
        return app(ToolContext::class);
    }

    protected function json(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    protected function settings(): ?Setting
    {
        return FilamentAi::settingsModel()::forCurrentTenant();
    }

    /**
     * Scope a query to the current tenant column (TallCMS soft-deps call this scopeSite).
     */
    protected function scopeSite(Builder $query, ?string $column = null): Builder
    {
        return $this->scopeTenant($query, $column);
    }

    protected function scopeTenant(Builder $query, ?string $column = null): Builder
    {
        $column ??= FilamentAi::tenantColumn();
        $tenantId = $this->context()->tenantId;

        if ($tenantId !== null) {
            return $query->where($column, $tenantId);
        }

        return $query->whereNull($column);
    }

    protected function assertAuthenticatedUser(): ?string
    {
        if ($this->context()->user !== null) {
            return null;
        }

        return app()->runningInConsole()
            ? null
            : 'No authenticated user for this chat.';
    }

    protected function assertProviderCapability(string $providerId, string $capability): ?string
    {
        if ($error = $this->assertAuthenticatedUser()) {
            return $error;
        }

        if ($this->settings()?->can($providerId, $capability)) {
            return null;
        }

        return "Capability \"{$providerId}.{$capability}\" is disabled. Enable it in AI Settings.";
    }
}
