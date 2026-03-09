<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->withoutGlobalScopes()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('filament-social-graph::feed_item.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament-social-graph::feed_item.section_metadata'))
                    ->columns(1)
                    ->schema([
                        TextEntry::make('actor')
                            ->label(__('filament-social-graph::feed_item.actor'))
                            ->formatStateUsing(fn ($record) => $record->actor?->name ?? $record->actor_type),
                        TextEntry::make('created_at')
                            ->label(__('filament-social-graph::feed_item.created_at'))
                            ->dateTime(),
                    ]),

                Section::make(__('filament-social-graph::feed_item.section_content'))
                    ->schema([
                        TextEntry::make('subject')
                            ->label(__('filament-social-graph::feed_item.subject')),
                        TextEntry::make('body')
                            ->label(__('filament-social-graph::feed_item.body'))
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('filament-social-graph::feed_item.attachments'))
                    ->schema([
                        ImageEntry::make('attachments_images')
                            ->label(__('filament-social-graph::feed_item.attachments'))
                            ->getStateUsing(fn ($record): array => array_map(
                                FeedItem::getAttachmentUrl(...),
                                array_values(array_filter(
                                    $record->attachments ?? [],
                                    FeedItem::isImagePath(...)
                                ))
                            ))
                            ->disk(FeedItem::getStorageDisk())
                            ->visibility('public')
                            ->imageHeight(192)
                            ->url(fn (string $state): string => $state)
                            ->openUrlInNewTab()
                            ->columnSpanFull()
                            ->hidden(fn ($record): bool => array_filter($record->attachments ?? [], FeedItem::isImagePath(...)) === []),
                        TextEntry::make('attachments_files')
                            ->label(__('filament-social-graph::feed_item.attachments_files'))
                            ->getStateUsing(fn ($record): array => array_values(array_filter(
                                $record->attachments ?? [],
                                fn (string $path): bool => ! FeedItem::isImagePath($path)
                            )))
                            ->formatStateUsing(function (array $paths): string {
                                if ($paths === []) {
                                    return '';
                                }
                                $links = array_map(
                                    fn (string $path): string => sprintf(
                                        '<a href="%s" target="_blank" rel="noopener" class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">%s</a>',
                                        e(FeedItem::getAttachmentUrl($path)),
                                        e(basename($path))
                                    ),
                                    $paths
                                );

                                return '<div class="flex flex-wrap gap-2">'.implode('', $links).'</div>';
                            })
                            ->html()
                            ->columnSpanFull()
                            ->hidden(fn ($record): bool => array_filter($record->attachments ?? [], fn (string $p): bool => ! FeedItem::isImagePath($p)) === []),
                    ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        $actorModels = config('filament-social-graph.actor_models', []);

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

                RichEditor::make('body')
                    ->label(__('filament-social-graph::feed_item.body'))
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->columnSpanFull(),

                FileUpload::make('attachments')
                    ->label(__('filament-social-graph::feed_item.attachments'))
                    ->multiple()
                    ->directory('feed-item-attachments')
                    ->disk(config('filesystems.default') === 's3' ? 's3' : 'public')
                    ->visibility('public')
                    ->columnSpanFull(),
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

                TextColumn::make('attachments_count')
                    ->label(__('filament-social-graph::feed_item.attachments_count'))
                    ->getStateUsing(fn (FeedItem $record): int => count($record->attachments ?? []))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $driver = $query->getConnection()->getDriverName();
                        $expr = match ($driver) {
                            'pgsql' => 'jsonb_array_length(attachments::jsonb)',
                            'sqlite' => 'json_array_length(attachments)',
                            default => 'JSON_LENGTH(attachments)',
                        };

                        return $query->orderByRaw("{$expr} {$direction}");
                    }),

                TextColumn::make('created_at')
                    ->label(__('filament-social-graph::feed_item.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
