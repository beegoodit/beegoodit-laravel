<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\Resources;

use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SocialPlatformResource extends Resource
{
    protected static ?string $model = SocialPlatform::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    public static function getModelLabel(): string
    {
        return __('filament-social-links::social.platform');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-links::social.platforms');
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

                Toggle::make('is_active')
                    ->label(__('filament-social-links::social.is_active')),

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
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\ListSocialPlatforms::route('/'),
            'create' => \BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\ListSocialPlatforms::route('/create'),
            'edit' => \BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialPlatformResource\Pages\ListSocialPlatforms::route('/{record}/edit'),
        ];
    }
}
