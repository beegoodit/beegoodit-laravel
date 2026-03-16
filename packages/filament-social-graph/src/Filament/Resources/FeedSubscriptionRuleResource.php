<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages;
use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedSubscriptionRuleRequest;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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

    public static function form(Schema $schema): Schema
    {
        $subscribableModels = config('filament-social-graph.subscribable_models', []);
        $scopes = config('filament-social-graph.subscription_rule_scopes', []);

        $subscribableTypes = collect($subscribableModels)->map(function (string $model): MorphToSelect\Type {
            $type = MorphToSelect\Type::make($model);
            if ($model === Feed::class) {
                return $type
                    ->titleAttribute('id')
                    ->getOptionLabelFromRecordUsing(
                        fn (\Illuminate\Database\Eloquent\Model $record): string => $record->owner?->name ?? $record->owner_type.' #'.($record->getKey() ?? '')
                    );
            }

            return $type->titleAttribute('name');
        })->all();

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('subscribable')
                    ->label(__('filament-social-graph::feed_subscription_rule.subscribable'))
                    ->types($subscribableTypes)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->hidden(empty($subscribableModels)),

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
                TextColumn::make('subscribable')
                    ->label(__('filament-social-graph::feed_subscription_rule.subscribable'))
                    ->formatStateUsing(function (FeedSubscriptionRule $record): string {
                        $subscribable = $record->subscribable;
                        if ($subscribable === null) {
                            return $record->subscribable_type.' #'.($record->subscribable_id ?? '');
                        }
                        if ($subscribable instanceof Feed) {
                            return $subscribable->owner?->name ?? $record->subscribable_type.' #'.$record->subscribable_id;
                        }

                        return $subscribable->name ?? $record->subscribable_type.' #'.$record->subscribable_id;
                    }),

                TextColumn::make('scope')
                    ->label(__('filament-social-graph::feed_subscription_rule.scope'))
                    ->formatStateUsing(fn (string $state): string => $scopes[$state] ?? $state),

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
}
