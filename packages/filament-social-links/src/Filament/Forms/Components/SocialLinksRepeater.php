<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\Forms\Components;

use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SocialLinksRepeater
{
    public static function make(string $name = 'socialLinks'): Repeater
    {
        return Repeater::make($name)
            ->relationship($name)
            ->schema([
                Select::make('social_platform_id')
                    ->label(__('filament-social-links::social.platform'))
                    ->options(fn () => SocialPlatform::query()
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                TextInput::make('handle')
                    ->label(__('filament-social-links::social.handle'))
                    ->placeholder(__('filament-social-links::social.handle_placeholder'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
            ])
            ->columns(3)
            ->defaultItems(0)
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => isset($state['social_platform_id'])
                    ? SocialPlatform::find($state['social_platform_id'])?->name.': '.($state['handle'] ?? '')
                    : null
            );
    }
}
