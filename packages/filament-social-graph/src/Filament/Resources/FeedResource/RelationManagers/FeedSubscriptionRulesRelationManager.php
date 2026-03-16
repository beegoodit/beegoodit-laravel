<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedSubscriptionRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'feedSubscriptionRules';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-social-graph::feed_resource.subscription_rules');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('filament-social-graph::feed_subscription_rule.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $scopes = config('filament-social-graph.subscription_rule_scopes', []);

        return $table
            ->columns([
                TextColumn::make('scope')
                    ->label(__('filament-social-graph::feed_subscription_rule.scope'))
                    ->formatStateUsing(fn (string $state): string => $scopes[$state] ?? $state),
                IconColumn::make('auto_subscribe')
                    ->label(__('filament-social-graph::feed_subscription_rule.auto_subscribe'))
                    ->boolean(),
                IconColumn::make('unsubscribable')
                    ->label(__('filament-social-graph::feed_subscription_rule.unsubscribable'))
                    ->boolean(),
            ]);
    }
}
