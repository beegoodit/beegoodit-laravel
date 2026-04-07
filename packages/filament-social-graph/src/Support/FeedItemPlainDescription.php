<?php

namespace BeegoodIT\FilamentSocialGraph\Support;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Str;

/**
 * Plain-text summary of a feed item for meta tags (Open Graph, etc.).
 */
final class FeedItemPlainDescription
{
    public static function for(FeedItem $item, int $limit = 200): string
    {
        $fromBody = self::normalizePlainText((string) ($item->body ?? ''));
        if ($fromBody !== '') {
            return Str::limit($fromBody, $limit, end: '...', preserveWords: true);
        }

        $subject = self::normalizePlainText((string) ($item->subject ?? ''));

        return $subject !== '' ? Str::limit($subject, $limit, end: '...', preserveWords: true) : '';
    }

    private static function normalizePlainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }
}
