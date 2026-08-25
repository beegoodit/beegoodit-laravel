<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources;

readonly class ParsedPublicResourceKey
{
    public function __construct(
        public string $publicId,
        public ?string $slug = null,
    ) {}
}
