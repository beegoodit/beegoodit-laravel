<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\RelationManagers\FeedItemsRelationManager;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\RelationManagers\FeedSubscriptionRulesRelationManager;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use Filament\Forms\Components\MorphToSelect;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedResource extends Resource
{
    protected static ?string $model = Feed::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rss';

    protected static ?int $navigationSort = 5;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'feeds';
    }

    public static function getModelLabel(): string
    {
        return __('filament-social-graph::feed_resource.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-graph::feed_resource.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-social-graph::feed_resource.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament-social-graph::feed_resource.plural');
    }

    public static function form(Schema $schema): Schema
    {
        $entityModels = config('filament-social-graph.entity_models', []);

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('owner')
                    ->label(__('filament-social-graph::feed_resource.owner'))
                    ->types(collect($entityModels)
                        ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->hidden(empty($entityModels)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner')
                    ->label(__('filament-social-graph::feed_resource.owner'))
                    ->formatStateUsing(fn (Feed $record): string => $record->owner?->name ?? $record->owner_type.' #'.$record->owner_id),

                TextColumn::make('created_at')
                    ->label(__('filament-social-graph::feed_item.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeds::route('/'),
            'create' => Pages\CreateFeed::route('/create'),
            'edit' => Pages\EditFeed::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            FeedItemsRelationManager::class,
            FeedSubscriptionRulesRelationManager::class,
        ];
    }
}
