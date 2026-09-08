<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Contracts;

interface RendererContract
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function htmlToPdf(string $html, array $options = []): string;
}
