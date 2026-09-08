<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Data\Din5008;

use Spatie\LaravelData\Data;

final class FolgeseiteLayoutData extends Data
{
    /**
     * @param  array<string, mixed>  $printOptions
     */
    public function __construct(
        public string $html,
        public array $printOptions,
    ) {}
}
