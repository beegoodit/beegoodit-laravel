<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Capabilities;

use BeegoodIT\FilamentAi\Capabilities\Contracts\CapabilityProvider;
use BeegoodIT\FilamentAi\Models\Setting;
use Laravel\Ai\Providers\Tools\WebFetch;
use Laravel\Ai\Providers\Tools\WebSearch;

/**
 * Package-owned shell capabilities (not tied to a host domain module).
 */
final class FilamentAiCapabilityProvider implements CapabilityProvider
{
    public const Id = 'filament_ai';

    public const WebAccess = 'web_access';

    public function id(): string
    {
        return self::Id;
    }

    public function contractVersion(): int
    {
        return CapabilityRegistry::SUPPORTED_CONTRACT_VERSION;
    }

    public function label(): string
    {
        return (string) __('filament-ai::ai.providers.filament_ai.label');
    }

    public function description(): ?string
    {
        return (string) __('filament-ai::ai.providers.filament_ai.description');
    }

    public function capabilities(): array
    {
        return [
            new CapabilityDefinition(
                key: self::WebAccess,
                label: (string) __('filament-ai::ai.capabilities.web_access'),
                help: (string) __('filament-ai::ai.capabilities.web_access_help'),
                default: false,
                allowedInReadOnlyMode: true,
                tools: [],
            ),
        ];
    }

    public function alwaysOnTools(): array
    {
        return [];
    }

    public function instructions(Setting $settings): ?string
    {
        if (! $settings->can(self::Id, self::WebAccess)) {
            return null;
        }

        $fragment = trim((string) __('filament-ai::ai.providers.filament_ai.instructions'));

        return $fragment !== '' ? $fragment : null;
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function providerNativeTools(string $capabilityKey, Setting $settings): array
    {
        if ($capabilityKey !== self::WebAccess) {
            return [];
        }

        return [
            new WebSearch,
            new WebFetch,
        ];
    }
}
