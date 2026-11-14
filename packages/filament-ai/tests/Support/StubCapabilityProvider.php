<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests\Support;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\Contracts\CapabilityProvider;
use BeegoodIT\FilamentAi\Models\Setting;

final class StubCapabilityProvider implements CapabilityProvider
{
    /**
     * @param  list<CapabilityDefinition>  $capabilities
     * @param  list<class-string>  $alwaysOnTools
     */
    public function __construct(
        private string $id = 'stub',
        private int $contractVersion = CapabilityRegistry::SUPPORTED_CONTRACT_VERSION,
        private string $label = 'Stub',
        private ?string $description = null,
        private array $capabilities = [],
        private array $alwaysOnTools = [],
        private ?string $instructions = null,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function contractVersion(): int
    {
        return $this->contractVersion;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function alwaysOnTools(): array
    {
        return $this->alwaysOnTools;
    }

    public function instructions(Setting $settings): ?string
    {
        return $this->instructions;
    }

    public function providerNativeTools(string $capabilityKey, Setting $settings): array
    {
        return [];
    }
}
