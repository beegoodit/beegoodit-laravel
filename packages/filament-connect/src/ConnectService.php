<?php

namespace Beegoodit\FilamentConnect;

abstract class ConnectService
{
    abstract public function getName(): string;

    abstract public function getFormSchema(): array;

    public function validate(array $credentials): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function discoverTournaments(array $credentials): array
    {
        return [];
    }
}
