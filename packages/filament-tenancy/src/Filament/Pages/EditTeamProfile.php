<?php

namespace BeegoodIT\FilamentTenancy\Filament\Pages;

use BeegoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Team Profile';
    }

    public function form(Schema $schema): Schema
    {
        $teamModel = $this->getTeamModel();

        return $schema
            ->components(
                BrandingSchema::getBaseSchema($teamModel)
            );
    }

    /**
     * Get the team model class.
     * Can be configured via config or will default to App\Models\Team.
     */
    protected function getTeamModel(): string
    {
        return config('filament-tenancy.team_model', \App\Models\Team::class);
    }

    /**
     * Hide from navigation since this is accessed via tenant menu.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
