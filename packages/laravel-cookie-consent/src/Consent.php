<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelCookieConsent;

use Closure;

class Consent
{
    public const CATEGORY_ESSENTIAL = 'essential';

    public const CATEGORY_FUNCTIONAL = 'functional';

    public const CATEGORY_ANALYTICS = 'analytics';

    public const CATEGORY_MARKETING = 'marketing';

    public const VALUE_ESSENTIAL = 'essential';

    public const VALUE_ANALYTICS = 'analytics';

    public const VALUE_MARKETING = 'marketing';

    public const VALUE_ALL = 'all';

    protected static ?Closure $policyUrlResolver = null;

    public static function cookieName(): string
    {
        $name = config('cookie-consent.cookie_name', config('cookie-consent.cookie_key', '__cookie_consent'));

        return is_string($name) && $name !== '' ? $name : '__cookie_consent';
    }

    public static function value(): ?string
    {
        $value = request()->cookie(self::cookieName());

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function available(): bool
    {
        return self::value() !== null;
    }

    /**
     * @param  self::CATEGORY_*  $category
     */
    public static function allows(string $category): bool
    {
        $value = self::value();

        return match ($category) {
            self::CATEGORY_ESSENTIAL => true,
            self::CATEGORY_FUNCTIONAL => $value === self::VALUE_ALL,
            self::CATEGORY_ANALYTICS => in_array($value, [self::VALUE_ANALYTICS, self::VALUE_ALL], true),
            self::CATEGORY_MARKETING => in_array($value, [self::VALUE_MARKETING, self::VALUE_ALL], true),
            default => false,
        };
    }

    public static function policyUrlUsing(?Closure $resolver): void
    {
        self::$policyUrlResolver = $resolver;
    }

    public static function policyUrl(): ?string
    {
        if (self::$policyUrlResolver instanceof Closure) {
            $resolved = call_user_func(self::$policyUrlResolver);

            if (! is_string($resolved) || $resolved === '') {
                return null;
            }

            return $resolved;
        }

        $locale = app()->getLocale();
        $localized = config("cookie-consent.policy_url_{$locale}");
        $fallback = config('cookie-consent.policy_url_en', config('cookie-consent.policy_url'));

        foreach ([$localized, $fallback] as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
