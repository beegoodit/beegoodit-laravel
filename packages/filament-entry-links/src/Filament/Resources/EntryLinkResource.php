<?php

namespace BeegoodIT\FilamentEntryLinks\Filament\Resources;

use BackedEnum;
use BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode;
use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages\CreateEntryLink;
use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages\EditEntryLink;
use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages\ListEntryLinks;
use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages\ViewEntryLink;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use BeegoodIT\FilamentEntryLinks\Support\EntryLinkQrSvg;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class EntryLinkResource extends Resource
{
    protected static ?string $model = EntryLink::class;

    protected static ?string $recordTitleAttribute = 'token';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.marketing');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getModelLabel(): string
    {
        return __('filament-entry-links::filament.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-entry-links::filament.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('token')
                            ->label(__('filament-entry-links::filament.form.token'))
                            ->required()
                            ->maxLength(64)
                            ->alphaNum()
                            ->unique(ignoreRecord: true)
                            ->helperText(fn (string $operation): ?string => $operation === 'create'
                                ? __('filament-entry-links::filament.form.token_auto_hint')
                                : null),

                        TextInput::make('slug')
                            ->label(__('filament-entry-links::filament.form.slug'))
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        TextInput::make('target_url')
                            ->label(__('filament-entry-links::filament.form.target_url'))
                            ->required()
                            ->url()
                            ->maxLength(2048),

                        Select::make('redirect_code')
                            ->label(__('filament-entry-links::filament.form.redirect_code'))
                            ->options(collect(EntryLinkRedirectCode::cases())->mapWithKeys(
                                fn (EntryLinkRedirectCode $code): array => [$code->value => $code->label()]
                            ))
                            ->required()
                            ->native(false),
                    ])
                    ->columnSpanFull(),

                Toggle::make('is_enabled')
                    ->label(__('filament-entry-links::filament.form.is_enabled'))
                    ->default(true)
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('active_from')
                            ->label(__('filament-entry-links::filament.form.active_from'))
                            ->seconds(true),

                        DateTimePicker::make('active_to')
                            ->label(__('filament-entry-links::filament.form.active_to'))
                            ->seconds(true),
                    ])
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label(__('filament-entry-links::filament.form.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        Group::make([
                            TextEntry::make('public_url')
                                ->label(__('filament-entry-links::filament.infolist.public_url'))
                                ->getStateUsing(fn (EntryLink $record): string => $record->publicUrl())
                                ->url(fn (EntryLink $record): string => $record->publicUrl())
                                ->openUrlInNewTab()
                                ->copyable()
                                ->columnSpanFull(),

                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('token')
                                        ->label(__('filament-entry-links::filament.form.token'))
                                        ->copyable(),

                                    TextEntry::make('slug')
                                        ->label(__('filament-entry-links::filament.form.slug'))
                                        ->placeholder('—'),
                                ])
                                ->columnSpanFull(),

                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('target_url')
                                        ->label(__('filament-entry-links::filament.form.target_url'))
                                        ->url(fn (EntryLink $record): string => $record->target_url)
                                        ->openUrlInNewTab(),

                                    TextEntry::make('redirect_code')
                                        ->label(__('filament-entry-links::filament.form.redirect_code'))
                                        ->formatStateUsing(fn (?EntryLinkRedirectCode $state): string => $state?->label() ?? ''),
                                ])
                                ->columnSpanFull(),

                            IconEntry::make('is_enabled')
                                ->label(__('filament-entry-links::filament.form.is_enabled'))
                                ->boolean()
                                ->columnSpanFull(),

                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('active_from')
                                        ->label(__('filament-entry-links::filament.form.active_from'))
                                        ->formatStateUsing(function (TextEntry $component): string {
                                            $record = $component->getRecord();

                                            if (! $record instanceof EntryLink) {
                                                return '';
                                            }

                                            if ($record->isOpenEndedActiveFrom()) {
                                                return __('filament-entry-links::filament.table.active_window_always');
                                            }

                                            return $record->active_from->format('Y-m-d H:i:s');
                                        }),

                                    TextEntry::make('active_to')
                                        ->label(__('filament-entry-links::filament.form.active_to'))
                                        ->formatStateUsing(function (TextEntry $component): string {
                                            $record = $component->getRecord();

                                            if (! $record instanceof EntryLink) {
                                                return '';
                                            }

                                            if ($record->isOpenEndedActiveTo()) {
                                                return __('filament-entry-links::filament.table.active_window_always');
                                            }

                                            return $record->active_to->format('Y-m-d H:i:s');
                                        }),
                                ])
                                ->columnSpanFull(),

                            TextEntry::make('notes')
                                ->label(__('filament-entry-links::filament.form.notes'))
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                            ->columns(1)
                            ->columnSpan(1),

                        Group::make([
                            TextEntry::make('entry_qr')
                                ->label(__('filament-entry-links::filament.infolist.qr_code'))
                                ->html()
                                ->getStateUsing(function (EntryLink $record): HtmlString {
                                    return EntryLinkQrSvg::inlineHtml(
                                        $record->publicUrl(),
                                        __('filament-entry-links::filament.infolist.qr_code_a11y'),
                                    );
                                })
                                ->columnSpanFull(),

                            TextEntry::make('created_at')
                                ->label(__('filament-entry-links::filament.infolist.created_at'))
                                ->dateTime()
                                ->columnSpanFull(),

                            TextEntry::make('updated_at')
                                ->label(__('filament-entry-links::filament.infolist.updated_at'))
                                ->dateTime()
                                ->columnSpanFull(),
                        ])
                            ->columns(1)
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('token')
                    ->label(__('filament-entry-links::filament.table.token'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('filament-entry-links::filament.table.slug'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('target_url')
                    ->label(__('filament-entry-links::filament.table.target_url'))
                    ->limit(40)
                    ->tooltip(fn (EntryLink $record): string => $record->target_url),

                TextColumn::make('redirect_code')
                    ->label(__('filament-entry-links::filament.table.redirect_code'))
                    ->formatStateUsing(fn (?EntryLinkRedirectCode $state): string => $state?->label() ?? ''),

                IconColumn::make('is_enabled')
                    ->label(__('filament-entry-links::filament.table.is_enabled'))
                    ->boolean(),

                TextColumn::make('active_from')
                    ->label(__('filament-entry-links::filament.table.active_from'))
                    ->formatStateUsing(function ($state, EntryLink $record): string {
                        if ($record->isOpenEndedActiveFrom()) {
                            return __('filament-entry-links::filament.table.active_window_always');
                        }

                        return $state instanceof Carbon
                            ? $state->format('Y-m-d H:i:s')
                            : '';
                    })
                    ->sortable(),

                TextColumn::make('active_to')
                    ->label(__('filament-entry-links::filament.table.active_to'))
                    ->formatStateUsing(function ($state, EntryLink $record): string {
                        if ($record->isOpenEndedActiveTo()) {
                            return __('filament-entry-links::filament.table.active_window_always');
                        }

                        return $state instanceof Carbon
                            ? $state->format('Y-m-d H:i:s')
                            : '';
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament-entry-links::filament.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntryLinks::route('/'),
            'create' => CreateEntryLink::route('/create'),
            'view' => ViewEntryLink::route('/{record}'),
            'edit' => EditEntryLink::route('/{record}/edit'),
        ];
    }
}
