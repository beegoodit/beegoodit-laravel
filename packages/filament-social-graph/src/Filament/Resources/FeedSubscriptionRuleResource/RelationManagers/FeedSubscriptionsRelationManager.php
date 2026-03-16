<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\RelationManagers;

use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedSubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'feedSubscriptions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-social-graph::feed_subscription.plural');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('filament-social-graph::feed_subscription.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
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
            ->defaultSort('created_at', 'desc');
    }
}
