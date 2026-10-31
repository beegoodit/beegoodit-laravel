<?php

namespace BeegoodIT\FilamentUserProfile\Enums;

enum SignInMethod: string
{
    case Password = 'password';
    case EmailCode = 'email_code';

    /**
     * @return array<int, self>
     */
    public static function configured(): array
    {
        $configured = config('filament-user-profile.sign_in_methods', [self::Password->value]);

        if (! is_array($configured)) {
            return [self::Password];
        }

        $methods = [];

        foreach ($configured as $value) {
            if (! is_string($value)) {
                continue;
            }

            $method = self::tryFrom($value);

            if ($method === null || in_array($method, $methods, true)) {
                continue;
            }

            $methods[] = $method;
        }

        if ($methods === []) {
            return [self::Password];
        }

        return $methods;
    }

    public static function enabled(self $method): bool
    {
        return in_array($method, self::configured(), true);
    }

    public static function primary(): self
    {
        return self::configured()[0];
    }
}
