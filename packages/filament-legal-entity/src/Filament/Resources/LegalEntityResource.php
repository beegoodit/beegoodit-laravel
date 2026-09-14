<?php

namespace BeegoodIT\FilamentLegalEntity\Filament\Resources;

use BeegoodIT\FilamentLegalEntity\Filament\LegalEntityForm;
use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages\CreateLegalEntity;
use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages\EditLegalEntity;
use BeegoodIT\FilamentLegalEntity\Filament\Resources\LegalEntityResource\Pages\ListLegalEntities;
use BeegoodIT\FilamentLegalEntity\Models\LegalEntity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LegalEntityResource extends Resource
{
    protected static ?string $model = LegalEntity::class;

    protected static ?string $tenantOwnershipRelationshipName = 'owner';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static \UnitEnum|string|null $navigationGroup = 'Legal';

    public static function getModelLabel(): string
    {
        return __('filament-legal-entity::entity.name_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-legal-entity::entity.name_plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-legal-entity::entity.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(LegalEntityForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('filament-legal-entity::entity.empty'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-legal-entity::entity.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner')
                    ->label(__('filament-legal-entity::entity.owner'))
                    ->formatStateUsing(fn (LegalEntity $record): string => $record->owner?->name ?? __('filament-legal-entity::entity.owner_platform'))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('owner_type', $direction)->orderBy('owner_id', $direction))
                    ->hidden(fn (): bool => self::filamentHasTenancy()),
                TextColumn::make('country')
                    ->label(__('filament-legal-entity::entity.country'))
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament-legal-entity::entity.email'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['owner']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalEntities::route('/'),
            'create' => CreateLegalEntity::route('/create'),
            'edit' => EditLegalEntity::route('/{record}/edit'),
        ];
    }

    private static function filamentHasTenancy(): bool
    {
        try {
            return filament()->hasTenancy();
        } catch (\Throwable) {
            return false;
        }
    }
}
