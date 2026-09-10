<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Tests\Fixtures;

use BeegoodIT\FilamentGroupSettings\Pages\GroupSettingsPage;
use Filament\Schemas\Schema;

class DummyUngroupedSettings extends GroupSettingsPage
{
    public static function getGroupKey(): string
    {
        return 'ungrouped';
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }
}
