<?php

namespace BeegoodIT\FilamentPartners\Filament\RelationManagers;

use BeegoodIT\FilamentPartners\Filament\PartnerForm;
use BeegoodIT\FilamentPartners\Filament\PartnerTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'partners';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-partners::partner.plural');
    }

    public function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema, forRelationManager: true);
    }

    public function table(Table $table): Table
    {
        return PartnerTable::configure($table, forRelationManager: true);
    }
}
