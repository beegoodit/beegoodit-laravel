<?php

namespace BeeGoodIT\FilamentTenancy\Filament\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class BrandingSchema
{
    /**
     * Get the branding section schema with logo, primary_color, and secondary_color fields.
     *
     * @return Section
     */
    public static function getBrandingSection(): Section
    {
        return Section::make('Branding')
            ->schema([
                FileUpload::make('logo')
                    ->label('Team Logo')
                    ->image()
                    ->disk(config('filesystems.default') === 's3' ? 's3' : 'public')
                    ->directory(fn () => sprintf('teams/logo/%s', Filament::getTenant()->id))
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                    ->helperText('Upload your team logo (JPG, PNG, GIF, WebP or SVG, max 2MB)')
                    ->visibility('public')
                    ->deletable()
                    ->moveFiles(),

                ColorPicker::make('primary_color')
                    ->label('Primary Brand Color')
                    ->helperText('Main color for buttons, links, and accents'),

                ColorPicker::make('secondary_color')
                    ->label('Secondary Color')
                    ->helperText('Additional brand color if needed'),
            ]);
    }

    /**
     * Get the base schema with name, slug, and branding section.
     *
     * @param string $teamModelClass The Team model class name for unique validation
     * @return array
     */
    public static function getBaseSchema(string $teamModelClass): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique($teamModelClass, 'slug', ignoreRecord: true)
                ->helperText('URL-friendly identifier. Auto-generated from name, but can be customized.'),

            self::getBrandingSection(),
        ];
    }
}

