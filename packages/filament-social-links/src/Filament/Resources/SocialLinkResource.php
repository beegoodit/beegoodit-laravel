<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\Resources;

use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource\Pages\CreateSocialLink;
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource\Pages\EditSocialLink;
use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource\Pages\ListSocialLinks;
use BeegoodIT\FilamentSocialLinks\Models\SocialLink;
use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static UnitEnum|string|null $navigationGroup = 'filament-social-links::social.navigation_group';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('filament-social-links::social.link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-links::social.links');
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
            ->columns(2)
            ->components([
                Select::make('social_platform_id')
                    ->label(__('filament-social-links::social.platform'))
                    ->options(SocialPlatform::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->columnSpan(1),

                TextInput::make('handle')
                    ->label(__('filament-social-links::social.handle'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder('@username')
                    ->columnSpan(1),

                TextInput::make('linkable_type')
                    ->label(__('filament-social-links::social.linkable_type'))
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(1),

                TextInput::make('linkable_id')
                    ->label(__('filament-social-links::social.linkable_id'))
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform.name')
                    ->label(__('filament-social-links::social.platform'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('handle')
                    ->label(__('filament-social-links::social.handle'))
                    ->searchable(),

                TextColumn::make('url')
                    ->label(__('filament-social-links::social.url'))
                    ->limit(40)
                    ->url(fn (SocialLink $record): string => $record->url, shouldOpenInNewTab: true),

                TextColumn::make('linkable_type')
                    ->label(__('filament-social-links::social.linkable_type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),

                TextColumn::make('linkable_id')
                    ->label(__('filament-social-links::social.linkable_id')),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListSocialLinks::route('/'),
            'create' => CreateSocialLink::route('/create'),
            'edit' => EditSocialLink::route('/{record}/edit'),
        ];
    }
}
