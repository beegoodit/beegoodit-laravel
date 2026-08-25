<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources;

final class PublicResourceKey
{
    /**
     * Parse a route segment as `{slug}-{publicId}` or `{publicId}`.
     *
     * Lookup must use {@see ParsedPublicResourceKey::$publicId} only; ignore slug.
     */
    public static function parse(string $segment, ?int $publicIdLength = null): ?ParsedPublicResourceKey
    {
        $publicIdLength ??= PublicId::LENGTH;
        $segment = trim($segment);

        if ($segment === '') {
            return null;
        }

        if (PublicId::isValid($segment, $publicIdLength)) {
            return new ParsedPublicResourceKey(PublicId::normalize($segment), null);
        }

        $suffix = substr($segment, -($publicIdLength + 1));

        if (! str_starts_with($suffix, '-')) {
            return null;
        }

        $publicId = substr($suffix, 1);

        if (! PublicId::isValid($publicId, $publicIdLength)) {
            return null;
        }

        $slug = substr($segment, 0, -($publicIdLength + 1));

        if ($slug === '' || str_ends_with($slug, '-')) {
            return null;
        }

        return new ParsedPublicResourceKey(PublicId::normalize($publicId), $slug);
    }

    public static function format(?string $slug, string $publicId, ?int $publicIdLength = null): string
    {
        $publicId = PublicId::assertValid($publicId, $publicIdLength);
        $slug = $slug !== null ? trim($slug, '-') : '';

        if ($slug === '') {
            return $publicId;
        }

        return $slug.'-'.$publicId;
    }
}
