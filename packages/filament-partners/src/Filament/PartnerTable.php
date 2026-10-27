<?php

namespace BeegoodIT\FilamentPartners\Filament;

use BeegoodIT\FilamentPartners\Enums\PartnerType;
use BeegoodIT\FilamentPartners\Models\Partner;
use BeegoodIT\FilamentPartners\Support\PartnerableTypes;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnerTable
{
    public static function configure(Table $table, bool $forRelationManager = false): Table
    {
        $table = $table
            ->columns(self::columns($forRelationManager))
            ->defaultSort('position')
            ->reorderable('position');

        if ($forRelationManager) {
            return $table
                ->recordTitleAttribute('name')
                ->headerActions([
                    CreateAction::make(),
                ])
                ->recordActions([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
        }

        return $table
            ->afterReordering(function (array $order): void {
                $partners = Partner::query()->whereIn((new Partner)->getKeyName(), $order)->get();
                foreach ($partners->groupBy(fn (Partner $p): string => $p->partnerable_type.'|'.($p->partnerable_id ?? 'null')) as $group) {
                    $ids = $group->pluck((new Partner)->getKeyName())->values()->all();
                    $first = $group->first();
                    Partner::setNewOrder(
                        $ids,
                        1,
                        null,
                        fn (Builder $query): Builder => $query
                            ->where('partnerable_type', $first->partnerable_type)
                            ->where('partnerable_id', $first->partnerable_id)
                    );
                }
            })
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    /**
     * @return array<int, ImageColumn|TextColumn|IconColumn>
     */
    private static function columns(bool $forRelationManager): array
    {
        return [
            ImageColumn::make('logo')
                ->label(__('filament-partners::partner.logo_label'))
                ->disk(config('filesystems.default') === 's3' ? 's3' : 'public')
                ->circular()
                ->defaultImageUrl(fn (Partner $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&size=64'),
            TextColumn::make('partnerable')
                ->label(__('filament-partners::partner.partnerable_label'))
                ->formatStateUsing(fn (Partner $record): string => PartnerableTypes::label($record->partnerable))
                ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('partnerable_type', $direction)->orderBy('partnerable_id', $direction))
                ->hidden(fn (): bool => $forRelationManager || Filament::hasTenancy()),
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
                ->icon(fn (?string $state): ?string => in_array($state, [null, '', '0'], true) ? null : 'heroicon-o-arrow-top-right-on-square')
                ->url(fn (?string $state): ?string => in_array($state, [null, '', '0'], true) ? null : $state)
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
        ];
    }
}
