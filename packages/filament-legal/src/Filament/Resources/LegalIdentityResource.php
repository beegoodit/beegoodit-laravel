<?php

namespace BeegoodIT\FilamentLegal\Filament\Resources;

use BeegoodIT\FilamentLegal\FilamentLegalPlugin;
use BeegoodIT\FilamentLegal\Filament\Resources\LegalIdentityResource\Pages;
use BeegoodIT\FilamentLegal\Models\LegalIdentity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegalIdentityResource extends Resource
{
    protected static ?string $model = LegalIdentity::class;

    protected static ?string $tenantOwnershipRelationshipName = 'owner';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';

    protected static \UnitEnum|string|null $navigationGroup = 'Legal';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-legal::messages.Legal');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-legal::messages.Legal Identities');
    }

    public static function getBreadcrumb(): string
    {
        return __('filament-legal::messages.Legal Identities');
    }

    public static function getLabel(): ?string
    {
        return __('filament-legal::messages.Legal Identity');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-legal::messages.Legal Identities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                MorphToSelect::make('owner')
                    ->label(__('filament-legal::messages.Owner'))
                    ->types(collect(FilamentLegalPlugin::get()->getLegalableModels())
                        ->map(fn ($model) => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->toArray())
                    ->columnSpanFull()
                    ->hidden(fn () => filament()->hasTenancy())
                    ->required(fn () => ! filament()->hasTenancy()),
                TextInput::make('name')
                    ->label(__('filament-legal::messages.Name'))
                    ->required(),
                TextInput::make('form')
                    ->label(__('filament-legal::messages.Legal Form')),
                TextInput::make('representative')
                    ->label(__('filament-legal::messages.Representative')),
                TextInput::make('email')
                    ->label(__('filament-legal::messages.Email'))
                    ->email(),
                TextInput::make('phone')
                    ->label(__('filament-legal::messages.Phone'))
                    ->tel(),
                TextInput::make('vat_id')
                    ->label(__('filament-legal::messages.VAT ID')),
                TextInput::make('register_court')
                    ->label(__('filament-legal::messages.Register Court')),
                TextInput::make('register_number')
                    ->label(__('filament-legal::messages.Register Number')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('filament-legal::messages.No legal identities found'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-legal::messages.Name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner_type')
                    ->label(__('filament-legal::messages.Owner Type'))
                    ->sortable(),
                TextColumn::make('owner_id')
                    ->label(__('filament-legal::messages.Owner ID'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('filament-legal::messages.Email'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('owner_type')
                    ->label(__('filament-legal::messages.Owner Type'))
                    ->options(collect(FilamentLegalPlugin::get()->getLegalableModels())
                        ->mapWithKeys(fn ($model) => [$model => __('filament-legal::messages.' . class_basename($model))])
                        ->toArray()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('filament-legal::messages.View')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalIdentities::route('/'),
            'create' => Pages\CreateLegalIdentity::route('/create'),
            'view' => Pages\ViewLegalIdentity::route('/{record}'),
            'edit' => Pages\EditLegalIdentity::route('/{record}/edit'),
        ];
    }
}
