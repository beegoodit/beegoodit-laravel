<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources;

use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;
use BeegoodIT\LaravelPwa\Models\Notifications\Broadcast;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getModelLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('laravel-pwa::notifications.nav.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label');
    }

    public static function getPluralLabel(): string
    {
        return __('laravel-pwa::notifications.broadcasts.resource_label_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->disabled(fn ($record) => $record?->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\Completed::class))
            ->components([
                Section::make()
                    ->schema([
                        Placeholder::make('trigger_type')
                            ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                            ->content(fn ($record) => $record?->target_ids ? __('laravel-pwa::broadcast.fields.target_type.options.users') : __('laravel-pwa::broadcast.fields.target_type.options.all')),

                        Placeholder::make('status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->content(fn ($record) => new HtmlString(view('filament::components.badge', [
                                'color' => match ($record?->status->getValue()) {
                                    'pending' => 'gray',
                                    'processing' => 'warning',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    default => 'gray',
                                },
                                'slot' => __("laravel-pwa::broadcast.fields.status.options.{$record?->status->getValue()}"),
                            ])->render())),

                        Placeholder::make('total_recipients')
                            ->label(__('laravel-pwa::broadcast.fields.total_recipients.label'))
                            ->content(fn ($record) => $record?->total_recipients ?? 0),

                        Placeholder::make('total_sent')
                            ->label(__('laravel-pwa::broadcast.fields.total_sent.label'))
                            ->content(fn ($record) => $record?->total_sent ?? 0),

                        Placeholder::make('total_opened')
                            ->label(__('laravel-pwa::broadcast.fields.total_opened.label'))
                            ->content(fn ($record) => $record?->total_opened ?? 0),
                    ])->columns(1),

                Section::make(__('laravel-pwa::notifications.broadcasts.content'))
                    ->schema([
                        TextInput::make('display_title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->placeholder(fn ($record) => $record?->payload['title'] ?? Str::afterLast($record?->trigger_type, '\\'))
                            ->disabled(),

                        Textarea::make('display_body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->placeholder(fn ($record) => $record?->payload['body'] ?? '-')
                            ->disabled(),

                        TextInput::make('display_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->placeholder(fn ($record) => $record?->payload['data']['url'] ?? '-')
                            ->disabled(),
                    ])->columns(1),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('laravel-pwa::notifications.broadcasts.stats'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('laravel-pwa::broadcast.fields.status.label'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                        TextEntry::make('total_recipients')
                            ->label(__('laravel-pwa::broadcast.fields.total_recipients.label')),

                        TextEntry::make('total_sent')
                            ->label(__('laravel-pwa::broadcast.fields.total_sent.label')),

                        TextEntry::make('total_opened')
                            ->label(__('laravel-pwa::broadcast.fields.total_opened.label')),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('laravel-pwa::notifications.broadcasts.content'))
                    ->schema([
                        TextEntry::make('display_title')
                            ->label(__('laravel-pwa::broadcast.fields.title.label'))
                            ->state(fn ($record) => $record->payload['title'] ?? Str::afterLast($record->trigger_type, '\\'))
                            ->weight('bold'),

                        TextEntry::make('display_body')
                            ->label(__('laravel-pwa::broadcast.fields.body.label'))
                            ->state(fn ($record) => $record->payload['body'] ?? '-'),

                        TextEntry::make('display_url')
                            ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                            ->state(fn ($record) => $record->payload['data']['url'] ?? '-')
                            ->url(fn ($record) => $record->payload['data']['url'] ?? null, true),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('laravel-pwa::broadcast.fields.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('display_title')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->state(fn ($record) => $record->payload['title'] ?? Str::afterLast($record->trigger_type, '\\'))
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->payload['body'] ?? ($record->status === 'automated' ? $record::class : null))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('trigger_type_display')
                    ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                    ->state(fn ($record) => $record->target_ids ? __('laravel-pwa::broadcast.fields.target_type.options.users') : __('laravel-pwa::broadcast.fields.target_type.options.all'))
                    ->size('xs')
                    ->color('gray'),

                TextColumn::make('display_url')
                    ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                    ->state(fn ($record) => $record->payload['data']['url'] ?? '-')
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->payload['data']['url'] ?? null, true)
                    ->color('primary')
                    ->size('xs')
                    ->limit(20)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('laravel-pwa::broadcast.fields.status.label'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("laravel-pwa::broadcast.fields.status.options.{$state}")),

                TextColumn::make('total_recipients')
                    ->label(__('laravel-pwa::broadcast.fields.total_recipients.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_sent')
                    ->label(__('laravel-pwa::broadcast.fields.total_sent.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_opened')
                    ->label(__('laravel-pwa::broadcast.fields.total_opened.label'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('hold')
                    ->label(__('laravel-pwa::notifications.messages.actions.hold'))
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Broadcast $record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class))
                    ->action(function (Broadcast $record): void {
                        $record->hold();

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.held.title'))
                            ->success()
                            ->send();
                    }),

                Action::make('release')
                    ->label(__('laravel-pwa::notifications.messages.actions.release'))
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Broadcast $record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class) && $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class))
                    ->action(function (Broadcast $record): void {
                        $record->release();

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.released.title'))
                            ->success()
                            ->send();
                    }),

                Action::make('resend')
                    ->label(__('laravel-pwa::broadcast.buttons.resend'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Broadcast $record): bool => $record->status->canTransitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class) && ! $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class) && ! $record->status->equals(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class))
                    ->action(function (Broadcast $record): void {
                        $record->resend();

                        dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\ProcessBroadcastJob($record))
                            ->onQueue(config('pwa.notifications.queue', 'default'));

                        \Filament\Notifications\Notification::make()
                            ->title(__('laravel-pwa::broadcast.notifications.requeued.title'))
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'edit' => Pages\EditBroadcast::route('/{record}/edit'),
        ];
    }
}
