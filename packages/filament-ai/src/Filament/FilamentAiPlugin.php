<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament;

use BeegoodIT\FilamentAi\Filament\Pages\Assistant;
use BeegoodIT\FilamentAi\Filament\Pages\ChatConversation;
use BeegoodIT\FilamentAi\Filament\Pages\Conversations;
use BeegoodIT\FilamentAi\Filament\Pages\Overview;
use BeegoodIT\FilamentAi\Filament\Pages\Settings;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentAiPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-ai';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            Overview::class,
            Assistant::class,
            Conversations::class,
            ChatConversation::class,
            Settings::class,
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
        $plugin = filament()->getPlugin('filament-ai');

        return $plugin;
    }
}
