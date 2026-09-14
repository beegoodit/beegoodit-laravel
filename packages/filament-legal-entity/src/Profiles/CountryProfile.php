<?php

namespace BeegoodIT\FilamentLegalEntity\Profiles;

interface CountryProfile
{
    public function key(): string;

    /**
     * @return list<string>
     */
    public function allowedKeys(): array;

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public function normalize(array $profile): array;
}
