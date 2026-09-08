<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

use InvalidArgumentException;

/** Validates logo data-URIs for DIN layout briefkopf images. */
final class LogoDataUri
{
    /**
     * @throws InvalidArgumentException
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (! preg_match(
            '/^data:image\/(png|jpe?g|gif|webp|svg\+xml);base64,([A-Za-z0-9+\/]+={0,2})$/i',
            $value,
            $matches,
        )) {
            throw new InvalidArgumentException(
                'Logo must be a base64 data:image URI (png, jpeg, gif, webp, or svg+xml).',
            );
        }

        $decoded = base64_decode($matches[2], true);
        if ($decoded === false || $decoded === '') {
            throw new InvalidArgumentException('Logo data URI contains invalid base64 payload.');
        }

        return $value;
    }
}
