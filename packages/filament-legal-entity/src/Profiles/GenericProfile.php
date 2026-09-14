<?php

namespace BeegoodIT\FilamentLegalEntity\Profiles;

final class GenericProfile implements CountryProfile
{
    public function key(): string
    {
        return 'generic';
    }

    /**
     * @return list<string>
     */
    public function allowedKeys(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public function normalize(array $profile): array
    {
        return [];
    }
}
