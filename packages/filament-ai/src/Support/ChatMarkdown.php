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

        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return self::hardenExternalLinks($html);
    }

    /**
     * Escape user text and keep only http(s) markdown links.
     */
    public static function toUserHtml(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $parts = preg_split(
            '/(\[[^\]]+\]\(https?:\/\/[^\s)]+\))/u',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE,
        );

        if ($parts === false) {
            return e($text);
        }

        $html = '';

        foreach ($parts as $part) {
            if (preg_match('/^\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)$/u', $part, $match) === 1) {
                $html .= '<a href="'.e($match[2]).'" target="_blank" rel="noopener noreferrer">'.e($match[1]).'</a>';

                continue;
            }

            $html .= e($part);
        }

        return $html;
    }

    /**
     * Assistant markdown wrapped for Filament prose styles.
     */
    public static function toAssistantHtml(string $markdown): string
    {
        $html = self::toHtml($markdown);

        if ($html === '') {
            return '';
        }

        return '<div class="fi-prose">'.$html.'</div>';
    }

    protected static function hardenExternalLinks(string $html): string
    {
        $replaced = preg_replace_callback(
            '/<a\s([^>]*?)>/i',
            function (array $matches): string {
                $attrs = trim($matches[1]);

                if (! preg_match('/\btarget=/i', $attrs)) {
                    $attrs .= ' target="_blank"';
                }

                if (! preg_match('/\brel=/i', $attrs)) {
                    $attrs .= ' rel="noopener noreferrer"';
                }

                return '<a '.$attrs.'>';
            },
            $html,
        );

        return is_string($replaced) ? $replaced : $html;
    }
}
