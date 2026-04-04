<?php

namespace BeegoodIT\FilamentEntryLinks\Tests\Unit;

use BeegoodIT\FilamentEntryLinks\Support\EntryLinkQrSvg;
use BeegoodIT\FilamentEntryLinks\Tests\TestCase;
use Endroid\QrCode\QrCode;

class EntryLinkQrSvgTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(QrCode::class)) {
            $this->markTestSkipped('endroid/qr-code is not installed in this vendor tree.');
        }
    }

    public function test_inline_html_contains_svg(): void
    {
        $html = EntryLinkQrSvg::inlineHtml('https://example.com/entry', 'Test QR');

        $this->assertStringContainsString('<svg', (string) $html);
        $this->assertStringContainsString('role="img"', (string) $html);
    }
}
