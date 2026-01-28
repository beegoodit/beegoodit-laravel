<?php

namespace BeegoodIT\FilamentTenancyDomains;

use BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentTenancyDomainsPlugin implements Plugin
{
    protected array $domainableModels = [];

    public function getId(): string
    {
        return 'filament-tenancy-domains';
    }

    public function domainableModels(array $models): static
    {
        $this->domainableModels = $models;

        return $this;
    }

    public function getDomainableModels(): array
    {
        return $this->domainableModels;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                DomainResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return resolve(static::class);
    }
}
