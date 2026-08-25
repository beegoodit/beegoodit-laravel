<?php

namespace BeegoodIT\FilamentTenancy\Filament\Schemas;

use BeegoodIT\FilamentI18n\Facades\FilamentI18n;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Str;

class BrandingSchema
{
    /**
     * Name and slug.
     *
     * @param  string  $teamModelClass  The Team model class name for unique validation
     */
    public static function getIdentitySection(string $teamModelClass): Section
    {
        return Section::make(__('filament-tenancy::messages.Team'))
            ->schema([
                TextInput::make('name')
                    ->label(fn () => __('filament-tenancy::messages.models.team.attributes.name'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label(fn () => __('filament-tenancy::messages.models.team.attributes.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique($teamModelClass, 'slug', ignoreRecord: true)
                    ->helperText(__('filament-tenancy::messages.URL-friendly identifier. Auto-generated from name, but can be customized.')),
            ]);
    }

    /**
     * Public description (translatable rich text) for team profile pages and SEO.
     */
    public static function getDescriptionSection(): Section
    {
        return Section::make(__('filament-tenancy::messages.Description'))
            ->description(__('filament-tenancy::messages.Short intro for your public team page. Search engines use a plain-text snippet.'))
            ->schema(self::descriptionEditorComponents());
    }

    /**
     * Get the branding section schema with logo, primary_color, and secondary_color fields.
     */
    public static function getBrandingSection(): Section
    {
        return Section::make(__('filament-tenancy::messages.Branding'))
            ->schema([
                FileUpload::make('logo')
                    ->label(__('filament-tenancy::messages.Team Logo'))
                    ->image()
                    ->disk(config('filesystems.default') === 's3' ? 's3' : 'public')
                    ->directory(fn (): string => sprintf('teams/logo/%s', Filament::getTenant()->id))
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                    ->helperText(__('filament-tenancy::messages.Upload your team logo (JPG, PNG, GIF, WebP or SVG, max 2MB)'))
                    ->visibility('private')
                    ->deletable()
                    ->moveFiles(),

                ColorPicker::make('primary_color')
                    ->label(__('filament-tenancy::messages.Primary Brand Color'))
                    ->helperText(__('filament-tenancy::messages.Main color for buttons, links, and accents')),

                ColorPicker::make('secondary_color')
                    ->label(__('filament-tenancy::messages.Secondary Color'))
                    ->helperText(__('filament-tenancy::messages.Additional brand color if needed')),
            ]);
    }

    /**
     * @return list<Component>
     */
    public static function descriptionEditorComponents(): array
    {
        $locales = FilamentI18n::availableLocales();
        $options = FilamentI18n::localeOptionsWithFlags();

        if (count($locales) <= 1) {
            $locale = $locales[0] ?? config('app.locale', 'en');

            return [
                RichEditor::make("description.{$locale}")
                    ->hiddenLabel()
                    ->columnSpanFull(),
            ];
        }

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tabs\Tab::make($options[$locale] ?? strtoupper($locale))
                ->schema([
                    RichEditor::make("description.{$locale}")
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ]);
        }

        return [
            Tabs::make('description_locales')
                ->columnSpanFull()
                ->tabs($tabs),
        ];
    }

    /**
     * Get the base schema: Team (name/slug), Description, Branding.
     *
     * @param  string  $teamModelClass  The Team model class name for unique validation
     */
    public static function getBaseSchema(string $teamModelClass): array
    {
        return [
            self::getIdentitySection($teamModelClass),
            self::getDescriptionSection(),
            self::getBrandingSection(),
        ];
    }
}
