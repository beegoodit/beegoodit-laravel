<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Capabilities;

final class CapabilityDefinition
{
    /**
     * @param  list<class-string>  $tools
     * @param  list<string>  $requires  Same-provider capability keys that must also be enabled
     * @param  string|null  $group  Optional Settings UI group label (fieldset) within the provider section
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ?string $help,
        public readonly bool $default,
        public readonly bool $allowedInReadOnlyMode,
        public readonly array $tools,
        public readonly array $requires = [],
        public readonly ?string $group = null,
    ) {}
}
