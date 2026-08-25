<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources;

/**
 * Assemble public path segments from caller-supplied locale/mount/type strings.
 * Apps own i18n maps; this package does not.
 */
final class PublicResourcePath
{
    /**
     * @return string Path starting with `/`, no trailing slash (except root-only edge cases).
     */
    public static function collection(string $locale, string $mount, string $type): string
    {
        return self::join($locale, $mount, $type);
    }

    public static function member(
        string $locale,
        string $mount,
        string $type,
        ?string $slug,
        string $publicId,
    ): string {
        return self::join($locale, $mount, $type, PublicResourceKey::format($slug, $publicId));
    }

    public static function action(
        string $locale,
        string $mount,
        string $type,
        string $publicId,
        string $action,
    ): string {
        $publicId = PublicId::assertValid($publicId);
        $action = trim($action, '/');

        return self::join($locale, $mount, $type, $publicId, $action);
    }

    private static function join(string ...$parts): string
    {
        $segments = [];

        foreach ($parts as $part) {
            $part = trim($part, '/');

            if ($part !== '') {
                $segments[] = $part;
            }
        }

        return '/'.implode('/', $segments);
    }
}
