<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

final readonly class ConnectionTestResult
{
    public function __construct(
        public bool $successful,
        public string $title,
        public ?string $body = null,
    ) {}
}
