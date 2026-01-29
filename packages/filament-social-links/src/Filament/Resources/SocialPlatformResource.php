<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\Resources;

use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\CreateSocialPlatform;
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\EditSocialPlatform;
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\ListSocialPlatforms;
use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class SocialPlatformResource extends Resource
{
    protected static ?string $model = SocialPlatform::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-share';

    protected static UnitEnum|string|null $navigationGroup = 'filament-social-links::social.navigation_group';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('filament-social-links::social.platform');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-links::social.platforms');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-social-links::social.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament-social-links::social.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('base_url')
                            ->label(__('filament-social-links::social.base_url'))
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://instagram.com/'),

                        TextInput::make('icon')
                            ->label(__('filament-social-links::social.icon'))
                            ->placeholder('fab-instagram'),

                        TextInput::make('sort_order')
                            ->label(__('filament-social-links::social.sort_order'))
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('filament-social-links::social.is_active'))
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-social-links::social.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('base_url')
                    ->label(__('filament-social-links::social.base_url'))
                    ->limit(30),

                TextColumn::make('icon')
                    ->label(__('filament-social-links::social.icon')),

                IconColumn::make('is_active')
                    ->label(__('filament-social-links::social.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('filament-social-links::social.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialPlatforms::route('/'),
            'create' => CreateSocialPlatform::route('/create'),
            'edit' => EditSocialPlatform::route('/{record}/edit'),
        ];
    }
}
