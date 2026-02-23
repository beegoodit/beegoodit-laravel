<?php

namespace BeegoodIT\FilamentPartners\Filament\Resources;

use BeegoodIT\FilamentPartners\Enums\PartnerType;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\CreatePartner;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\EditPartner;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\ListPartners;
use BeegoodIT\FilamentPartners\Models\Partner;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $tenantOwnershipRelationshipName = 'partnerable';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return __('filament-partners::partner.name');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-partners::partner.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-partners::partner.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        $disk = config('filament-partners.logo_disk') ?? (config('filesystems.default') === 's3' ? 's3' : 'public');
        $directory = config('filament-partners.logo_directory', 'partners');
        $maxSize = config('filament-partners.logo_max_size', 2048);

        $partnerableModels = config('filament-partners.partnerable_models', []);

        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('partnerable')
                    ->label(__('filament-partners::partner.partnerable_label'))
                    ->types(collect($partnerableModels)
                        ->map(fn (string $model): MorphToSelect\Type => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->all())
                    ->columnSpanFull()
                    ->hidden(fn (): bool => \Filament\Facades\Filament::hasTenancy() || empty($partnerableModels))
                    ->required(false),
                Select::make('type')
                    ->label(__('filament-partners::partner.type_label'))
                    ->options(collect(PartnerType::cases())->mapWithKeys(fn (PartnerType $t): array => [$t->value => $t->label()]))
                    ->default(PartnerType::Partner)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('name')
                    ->label(__('filament-partners::partner.name_label'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                    FileUpload::make('logo')
                    ->label(__('filament-partners::partner.logo_label'))
                    ->image()
                    ->disk($disk)
                    ->directory(fn (?Partner $record): string => $record
                        ? $directory.'/'.$record->id
                        : $directory
                    )
                    ->maxSize($maxSize)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                    ->visibility('public')
                    ->deletable()
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label(__('filament-partners::partner.description_label'))
                    ->columnSpanFull(),

                TextInput::make('url')
                    ->label(__('filament-partners::partner.url_label'))
                    ->url()
                    ->maxLength(1024)
                    ->columnSpanFull(),

                DateTimePicker::make('active_from')
                    ->label(__('filament-partners::partner.active_from_label'))
                    ->required()
                    ->default(Carbon::parse('1970-01-01 00:00:00'))
                    ->columnSpan(1),

                DateTimePicker::make('active_to')
                    ->label(__('filament-partners::partner.active_to_label'))
                    ->required()
                    ->default(Carbon::parse('9999-12-31 23:59:59'))
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('filament-partners::partner.logo_label'))
                    ->disk(config('filament-partners.logo_disk') ?? (config('filesystems.default') === 's3' ? 's3' : 'public'))
                    ->circular()
                    ->defaultImageUrl(fn (Partner $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&size=64'),

                TextColumn::make('partnerable')
                    ->label(__('filament-partners::partner.partnerable_label'))
                    ->formatStateUsing(fn (Partner $record): string => $record->partnerable?->name ?? __('filament-partners::partner.partnerable_platform'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('partnerable_type', $direction)->orderBy('partnerable_id', $direction);
                    })
                    ->hidden(fn (): bool => \Filament\Facades\Filament::hasTenancy()),

                TextColumn::make('type')
                    ->label(__('filament-partners::partner.type_label'))
                    ->formatStateUsing(fn (PartnerType $state): string => $state->label())
                    ->badge(),

                TextColumn::make('name')
                    ->label(__('filament-partners::partner.name_label'))
                    ->limit(40)
                    ->sortable()
                    ->searchable(),

                IconColumn::make('url')
                    ->label(__('filament-partners::partner.url_label'))
                    ->icon(fn (?string $state): string | null => ! empty($state) ? 'heroicon-o-arrow-top-right-on-square' : null)
                    ->url(fn (?string $state): ?string => ! empty($state) ? $state : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder(''),

                TextColumn::make('position')
                    ->label(__('filament-partners::partner.position_label'))
                    ->sortable(),

                TextColumn::make('active')
                    ->label(__('filament-partners::partner.active_label'))
                    ->getStateUsing(fn (Partner $record): string => $record->activeAt(now())
                        ? __('filament-partners::partner.active_yes')
                        : __('filament-partners::partner.active_no'))
                    ->badge()
                    ->color(fn (Partner $record): string => $record->activeAt(now()) ? 'success' : 'gray'),

                TextColumn::make('active_from')
                    ->label(__('filament-partners::partner.active_from_label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('active_to')
                    ->label(__('filament-partners::partner.active_to_label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->afterReordering(function (array $order): void {
                $partners = Partner::query()->whereIn((new Partner)->getKeyName(), $order)->get();
                foreach ($partners->groupBy(fn (Partner $p): string => $p->partnerable_type.'|'.($p->partnerable_id ?? 'null')) as $group) {
                    $ids = $group->pluck((new Partner)->getKeyName())->values()->all();
                    $first = $group->first();
                    Partner::setNewOrder(
                        $ids,
                        1,
                        null,
                        fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                            ->where('partnerable_type', $first->partnerable_type)
                            ->where('partnerable_id', $first->partnerable_id)
                    );
                }
            })
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (\Filament\Facades\Filament::getTenant() !== null) {
            return $query;
        }

        // Admin: no tenant scope (show all partners: platform + team, etc.)
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
