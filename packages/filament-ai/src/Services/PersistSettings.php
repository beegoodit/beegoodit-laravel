<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Laravel\Ai\Enums\Lab;

class PersistSettings
{
    public function __construct(
        private CapabilityRegistry $registry,
    ) {}

    /**
     * @param  array{
     *     enabled?: bool,
     *     mcp_enabled?: bool,
     *     provider?: string|Lab|null,
     *     model?: string|null,
     *     api_key?: string|null,
     *     capabilities?: array<string, mixed>|null,
     *     custom_instructions?: string|null
     * }  $data
     */
    public function save(array $data, int|string|null $tenantId = null): Setting
    {
        $tenantId ??= FilamentAi::tenantId();
        $column = FilamentAi::tenantColumn();

        $settings = FilamentAi::settings()
            ->when(
                $tenantId === null,
                fn ($query) => $query->whereNull($column),
                fn ($query) => $query->where($column, $tenantId),
            )
            ->first() ?? new (FilamentAi::settingsModel());

        $settings->setTenantKey($tenantId);
        $settings->enabled = (bool) ($data['enabled'] ?? false);

        if (array_key_exists('mcp_enabled', $data)) {
            $settings->mcp_enabled = (bool) $data['mcp_enabled'];
        }

        $provider = $data['provider'] ?? null;
        $settings->provider = match (true) {
            $provider instanceof Lab => $provider,
            is_string($provider) && $provider !== '' => Lab::from($provider),
            default => null,
        };

        $settings->model = filled($data['model'] ?? null) ? (string) $data['model'] : null;

        if (array_key_exists('api_key', $data) && filled($data['api_key'])) {
            $settings->api_key = (string) $data['api_key'];
        }

        $capabilities = $data['capabilities'] ?? null;
        $settings->capabilities = is_array($capabilities)
            ? $this->registry->merge($capabilities)
            : $this->registry->defaults();

        if (array_key_exists('custom_instructions', $data)) {
            $instructions = trim((string) ($data['custom_instructions'] ?? ''));
            $settings->custom_instructions = $instructions !== '' ? $instructions : null;
        }

        $settings->save();

        return $settings->refresh();
    }
}
