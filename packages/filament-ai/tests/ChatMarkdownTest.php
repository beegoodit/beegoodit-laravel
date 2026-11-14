<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Support\ChatMarkdown;
use PHPUnit\Framework\Attributes\Test;

class ChatMarkdownTest extends TestCase
{
    #[Test]
    public function it_renders_github_flavored_markdown(): void
    {
        $html = ChatMarkdown::toHtml(<<<'MD'
### Snapshot

**bold** and a table:

| Billing state | Entries |
| --- | ---: |
| billable | 12 |
MD);

        $this->assertStringContainsString('<h3>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>', $html);
        $this->assertStringContainsString('billable', $html);
    }

    #[Test]
    public function it_strips_raw_html_and_rejects_unsafe_links(): void
    {
        $html = ChatMarkdown::toHtml('Inject <script>alert(1)</script> and [x](javascript:alert(1))');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    #[Test]
    public function it_returns_empty_string_for_empty_markdown(): void
    {
        $this->assertSame('', ChatMarkdown::toHtml(''));
    }
}
