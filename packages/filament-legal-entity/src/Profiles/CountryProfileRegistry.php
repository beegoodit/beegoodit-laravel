<?php

namespace BeegoodIT\FilamentLegalEntity\Profiles;

final class CountryProfileRegistry
{
    public static function for(?string $country): CountryProfile
    {
        if (strtoupper((string) $country) === 'DE') {
            return new DeProfile;
        }

        return new GenericProfile;
    }

    public static function isDe(?string $country): bool
    {
        return strtoupper((string) $country) === 'DE';
    }
}
