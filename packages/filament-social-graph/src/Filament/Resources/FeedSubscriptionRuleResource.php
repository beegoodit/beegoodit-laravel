<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\RelationManagers\FeedSubscriptionsRelationManager;
use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedSubscriptionRuleRequest;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedSubscriptionRuleResource extends Resource
{
    protected static ?string $model = FeedSubscriptionRule::class;

    protected static ?string $tenantOwnershipRelationshipName = 'team';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 15;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'feed-subscription-rules';
    }

    public static function getModelLabel(): string
    {
        return __('filament-social-graph::feed_subscription_rule.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-social-graph::feed_subscription_rule.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-social-graph::feed_subscription_rule.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->withoutGlobalScopes()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament-social-graph::feed_subscription_rule.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('feedSubscriptions');
    }

    public static function form(Schema $schema): Schema
    {
        $scopes = config('filament-social-graph.subscription_rule_scopes', []);

        return $schema
            ->columns(2)
            ->components([
                Select::make('feed_id')
                    ->label(__('filament-social-graph::feed_subscription_rule.feed'))
                    ->relationship(
                        name: 'feed',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn ($query) => $query->with('owner')
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Feed $record): string => $record->owner?->name ?? $record->owner_type.' #'.$record->getKey()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Select::make('scope')
                    ->label(__('filament-social-graph::feed_subscription_rule.scope'))
                    ->options($scopes)
                    ->required()
                    ->rules(StoreFeedSubscriptionRuleRequest::scopeValidationRules())
                    ->hidden(empty($scopes)),

                Toggle::make('auto_subscribe')
                    ->label(__('filament-social-graph::feed_subscription_rule.auto_subscribe'))
                    ->default(false),

                Toggle::make('unsubscribable')
                    ->label(__('filament-social-graph::feed_subscription_rule.unsubscribable'))
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        $scopes = config('filament-social-graph.subscription_rule_scopes', []);

        return $table
            ->columns([
                TextColumn::make('feed')
                    ->label(__('filament-social-graph::feed_subscription_rule.feed'))
                    ->formatStateUsing(function (FeedSubscriptionRule $record): string {
                        $feed = $record->feed;
                        if ($feed === null) {
                            return $record->feed_id ?? '';
                        }

                        return $feed->owner?->name ?? $feed->owner_type.' #'.$feed->getKey();
                    }),

                TextColumn::make('scope')
                    ->label(__('filament-social-graph::feed_subscription_rule.scope'))
                    ->formatStateUsing(fn (string $state): string => $scopes[$state] ?? $state),

                TextColumn::make('feed_subscriptions_count')
                    ->label(__('filament-social-graph::feed_subscription_rule.subscriptions_count'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('auto_subscribe')
                    ->label(__('filament-social-graph::feed_subscription_rule.auto_subscribe'))
                    ->boolean(),

                IconColumn::make('unsubscribable')
                    ->label(__('filament-social-graph::feed_subscription_rule.unsubscribable'))
                    ->boolean(),

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
            'index' => Pages\ListFeedSubscriptionRules::route('/'),
            'create' => Pages\CreateFeedSubscriptionRule::route('/create'),
            'edit' => Pages\EditFeedSubscriptionRule::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            FeedSubscriptionsRelationManager::class,
        ];
    }
}
