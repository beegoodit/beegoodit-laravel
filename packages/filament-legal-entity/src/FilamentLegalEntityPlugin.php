<?php

namespace BeegoodIT\FilamentLegalEntity;

use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentLegalEntityPlugin implements Plugin
{
    /** @var list<class-string> */
    protected array $legalableModels = [];

    protected bool $hasResource = true;

    /**
     * @param  list<class-string>  $models
     */
    public function legalableModels(array $models): static
    {
        $this->legalableModels = $models;

        return $this;
    }

    /**
     * @return list<class-string>
     */
    public function getLegalableModels(): array
    {
        return $this->legalableModels;
    }

    public function hasResource(bool $hasResource = true): static
    {
        $this->hasResource = $hasResource;

        return $this;
    }

    public function registersResource(): bool
    {
        return $this->hasResource;
    }

    public function getId(): string
    {
        return 'filament-legal-entity';
    }

    public function register(Panel $panel): void
    {
        if (! $this->hasResource) {
            return;
        }

        $panel->resources([
            LegalEntityResource::class,
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
        $plugin = filament()->getPlugin('filament-legal-entity');

        return $plugin;
    }
}
