<?php

namespace BeegoodIT\FilamentLegal;

use BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource;
use BeegoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentLegalPlugin implements Plugin
{
    protected array $legalableModels = [];

    public function legalableModels(array $models): static
    {
        $this->legalableModels = $models;

        return $this;
    }

    public function getLegalableModels(): array
    {
        return $this->legalableModels;
    }
    public function getId(): string
    {
        return 'filament-legal';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                LegalPolicyResource::class,
                LegalIdentityResource::class,
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

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin('filament-legal');

        return $plugin;
    }
}
