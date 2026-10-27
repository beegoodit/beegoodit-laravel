<?php

namespace BeegoodIT\FilamentPartners\Filament\Resources;

use BeegoodIT\FilamentPartners\Filament\PartnerForm;
use BeegoodIT\FilamentPartners\Filament\PartnerTable;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\CreatePartner;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\EditPartner;
use BeegoodIT\FilamentPartners\Filament\Resources\PartnerResource\Pages\ListPartners;
use BeegoodIT\FilamentPartners\Models\Partner;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $tenant = Filament::getTenant();

        if ($tenant instanceof Model) {
            return $query
                ->where('partnerable_type', $tenant->getMorphClass())
                ->where('partnerable_id', $tenant->getKey());
        }

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
