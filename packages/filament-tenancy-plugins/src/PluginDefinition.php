<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins;

final readonly class PluginDefinition
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $id,
        public string $label,
        public array $meta = [],
    ) {}
}
