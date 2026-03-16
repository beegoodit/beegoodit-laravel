<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'feedItems';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-social-graph::feed_resource.feed_items');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('filament-social-graph::feed_item.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label(__('filament-social-graph::feed_item.subject'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament-social-graph::feed_item.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
