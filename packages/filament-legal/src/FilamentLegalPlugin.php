<?php

namespace BeeGoodIT\FilamentLegal;

use BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentLegalPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-legal';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                LegalPolicyResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin('filament-legal');

        return $plugin;
    }
}
