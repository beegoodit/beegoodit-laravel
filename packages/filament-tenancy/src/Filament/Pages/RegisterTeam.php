<?php

namespace BeegoodIT\FilamentTenancy\Filament\Pages;

use BeegoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('filament-tenancy::messages.Register team');
    }

    public function form(Schema $schema): Schema
    {
        $teamModel = $this->getTeamModel();

        return $schema
            ->components(
                BrandingSchema::getBaseSchema($teamModel)
            );
    }

    protected function handleRegistration(array $data): Model
    {
        $teamModel = $this->getTeamModel();
        $team = $teamModel::create($data);

        // Attach the current user to the team
        $user = auth()->user();
        if ($user && method_exists($team, 'users')) {
            $team->users()->attach($user);
        } elseif ($user && method_exists($team, 'members')) {
            $team->members()->attach($user);
        }

        return $team;
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
