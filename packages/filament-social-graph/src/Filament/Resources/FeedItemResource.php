<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedItemResource extends Resource
{
    protected static ?string $model = FeedItem::class;

    protected static ?string $tenantOwnershipRelationshipName = 'team';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('filament-social-graph::feed_item.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-graph::feed_item.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-social-graph::feed_item.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('actor')
                    ->label(__('filament-social-graph::feed_item.actor'))
                    ->formatStateUsing(fn ($record) => $record->actor?->name ?? $record->actor_type),
                TextEntry::make('subject')
                    ->label(__('filament-social-graph::feed_item.subject')),
                TextEntry::make('body')
                    ->label(__('filament-social-graph::feed_item.body'))
                    ->markdown()
                    ->columnSpanFull(),
                TextEntry::make('visibility')
                    ->label(__('filament-social-graph::feed_item.visibility'))
                    ->formatStateUsing(fn ($state) => $state?->label()),
                TextEntry::make('created_at')
                    ->label(__('filament-social-graph::feed_item.created_at'))
                    ->dateTime(),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        $actorModels = config('filament-social-graph.actor_models', []);
        $teamModel = config('filament-social-graph.tenancy.team_model');

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('actor')
                    ->label(__('filament-social-graph::feed_item.actor'))
                    ->types(collect($actorModels)
                        ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->hidden(empty($actorModels)),

                TextInput::make('subject')
                    ->label(__('filament-social-graph::feed_item.subject'))
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('body')
                    ->label(__('filament-social-graph::feed_item.body'))
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('visibility')
                    ->label(__('filament-social-graph::feed_item.visibility'))
                    ->options(collect(Visibility::cases())->mapWithKeys(fn (Visibility $v): array => [$v->value => $v->label()]))
                    ->default(Visibility::Public)
                    ->required()
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('actor')
                    ->label(__('filament-social-graph::feed_item.actor'))
                    ->formatStateUsing(fn (FeedItem $record): string => $record->actor?->name ?? $record->actor_type)
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('filament-social-graph::feed_item.subject'))
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('body')
                    ->label(__('filament-social-graph::feed_item.body'))
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('visibility')
                    ->label(__('filament-social-graph::feed_item.visibility'))
                    ->formatStateUsing(fn (Visibility $state): string => $state->label())
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('filament-social-graph::feed_item.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('visibility')
                    ->label(__('filament-social-graph::feed_item.visibility'))
                    ->options(collect(Visibility::cases())->mapWithKeys(fn (Visibility $v): array => [$v->value => $v->label()])),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedItems::route('/'),
            'create' => Pages\CreateFeedItem::route('/create'),
            'view' => Pages\ViewFeedItem::route('/{record}'),
            'edit' => Pages\EditFeedItem::route('/{record}/edit'),
        ];
    }
}
