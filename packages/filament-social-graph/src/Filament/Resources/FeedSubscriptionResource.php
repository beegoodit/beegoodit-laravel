<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionResource\Pages;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use Filament\Forms\Components\MorphToSelect;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedSubscriptionResource extends Resource
{
    protected static ?string $model = FeedSubscription::class;

    protected static ?string $tenantOwnershipRelationshipName = 'team';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?int $navigationSort = 20;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'feed-subscriptions';
    }

    public static function getModelLabel(): string
    {
        return __('filament-social-graph::feed_subscription.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-graph::feed_subscription.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-social-graph::feed_subscription.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->withoutGlobalScopes()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament-social-graph::feed_subscription.plural');
    }

    public static function form(Schema $schema): Schema
    {
        $actorModels = config('filament-social-graph.actor_models', []);

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('subscriber')
                    ->label(__('filament-social-graph::feed_subscription.subscriber'))
                    ->types(collect($actorModels)
                        ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->hidden(empty($actorModels)),

                MorphToSelect::make('feedOwner')
                    ->label(__('filament-social-graph::feed_subscription.feed_owner'))
                    ->types(collect($actorModels)
                        ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->hidden(empty($actorModels)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscriber')
                    ->label(__('filament-social-graph::feed_subscription.subscriber'))
                    ->formatStateUsing(fn (FeedSubscription $record): string => $record->subscriber?->name ?? $record->subscriber_type),

                TextColumn::make('feedOwner')
                    ->label(__('filament-social-graph::feed_subscription.feed_owner'))
                    ->formatStateUsing(fn (FeedSubscription $record): string => $record->feedOwner?->name ?? $record->feed_owner_type),

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
            'index' => Pages\ListFeedSubscriptions::route('/'),
            'create' => Pages\CreateFeedSubscription::route('/create'),
            'edit' => Pages\EditFeedSubscription::route('/{record}/edit'),
        ];
    }
}
