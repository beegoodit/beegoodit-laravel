<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Illuminate\Support\Str;

final class ChatMarkdown
{
    /**
     * Render GitHub-flavored Markdown for chat bubbles.
     *
     * Raw HTML is stripped and unsafe links are rejected to reduce XSS risk
     * from model output.
     */
    public static function toHtml(string $markdown): string
    {
        if ($markdown === '') {
            return '';
        }

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
