<?php

namespace BeegoodIT\FilamentEntryLinks\Support;

class SegmentTokenParser
{
    /**
     * Extract the link token from `{token}` or `{token}-{slug...}`.
     * Token must not contain hyphens; everything before the first hyphen is the token.
     */
    public static function tokenFromSegment(string $segment): string
    {
        $segment = trim($segment);

        if ($segment === '') {
            return '';
        }

        $pos = strpos($segment, '-');

        if ($pos === false) {
            return $segment;
        }

        return substr($segment, 0, $pos);
    }
}
