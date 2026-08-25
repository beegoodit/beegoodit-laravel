<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources;

use InvalidArgumentException;
use Random\RandomException;

final class PublicId
{
    public const int LENGTH = 8;

    /**
     * Crockford base32 alphabet (lowercase). Omits i, l, o, u.
     */
    public const string ALPHABET = '0123456789abcdefghjkmnpqrstvwxyz';

    /**
     * @throws RandomException
     */
    public static function generate(?int $length = null): string
    {
        $length ??= self::LENGTH;
        $max = strlen(self::ALPHABET) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= self::ALPHABET[random_int(0, $max)];
        }

        return $id;
    }

    public static function normalize(string $value): string
    {
        return strtolower(trim($value));
    }

    public static function isValid(string $value, ?int $length = null): bool
    {
        $length ??= self::LENGTH;
        $value = self::normalize($value);

        if (strlen($value) !== $length) {
            return false;
        }

        return (bool) preg_match('/^['.preg_quote(self::ALPHABET, '/').']+$/', $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function assertValid(string $value, ?int $length = null): string
    {
        $normalized = self::normalize($value);

        if (! self::isValid($normalized, $length)) {
            throw new InvalidArgumentException(
                sprintf('Invalid public id [%s]; expected %d Crockford base32 characters.', $value, $length ?? self::LENGTH)
            );
        }

        return $normalized;
    }
}
