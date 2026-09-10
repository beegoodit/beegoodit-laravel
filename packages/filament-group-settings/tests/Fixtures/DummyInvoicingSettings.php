<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Tests\Fixtures;

use BeegoodIT\FilamentGroupSettings\Pages\GroupSettingsPage;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DummyInvoicingSettings extends GroupSettingsPage
{
    public static function getGroupKey(): string
    {
        return 'invoicing';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Invoicing';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('flag'),
            ]);
    }
}
