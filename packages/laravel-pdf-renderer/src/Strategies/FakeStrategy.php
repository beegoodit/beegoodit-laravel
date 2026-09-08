<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Strategies;

use BeegoodIT\Pdf\Contracts\RendererContract;

/** Minimal PDF bytes for tests / environments without Chromium. */
final class FakeStrategy implements RendererContract
{
    public function htmlToPdf(string $html, array $options = []): string
    {
        return "%PDF-1.4\n%FakeStrategy\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".md5($html);
    }
}
