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
        $this->assertSame('', ChatMarkdown::toUserHtml(''));
        $this->assertSame('', ChatMarkdown::toAssistantHtml(''));
    }

    #[Test]
    public function user_html_escapes_text_and_keeps_http_links(): void
    {
        $html = ChatMarkdown::toUserHtml('See [docs](https://example.com/docs) and <script>alert(1)</script>');

        $this->assertStringContainsString('href="https://example.com/docs"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function assistant_html_uses_filament_prose_and_hardens_links(): void
    {
        $html = ChatMarkdown::toAssistantHtml('See [docs](https://example.com/docs)');

        $this->assertStringStartsWith('<div class="fi-prose">', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }
}
