<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Capabilities\Contracts;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Models\Setting;

/**
 * Capability provider SPI (v1). Method names stay sync-compatible with TallCMS soft-deps after pack-rewrite.
 */
interface CapabilityProvider
{
    /**
     * Stable snake_case id (e.g. tallcms, speisekarte).
     */
    public function id(): string;

    /**
     * SPI contract major version this provider targets.
     */
    public function contractVersion(): int;

    /**
     * Filament section title.
     */
    public function label(): string;

    /**
     * Optional section description / help.
     */
    public function description(): ?string;

    /**
     * @return list<CapabilityDefinition>
     */
    public function capabilities(): array;

    /**
     * Tools always registered when AI is enabled (not capability-gated).
     *
     * @return list<class-string>
     */
    public function alwaysOnTools(): array;

    /**
     * Optional system-instruction fragment when this provider has any enabled group.
     */
    public function instructions(Setting $settings): ?string;

    /**
     * Provider-native Laravel AI tools (e.g. WebSearch) for a capability key.
     *
     * @return list<object>
     */
    public function providerNativeTools(string $capabilityKey, Setting $settings): array;
}
