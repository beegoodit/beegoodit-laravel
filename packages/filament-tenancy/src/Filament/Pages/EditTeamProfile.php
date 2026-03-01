<?php

namespace BeegoodIT\FilamentTenancy\Filament\Pages;

use BeegoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Routing\Router;

class EditTeamProfile extends EditTenantProfile
{
    /**
     * Register routes so the route name is filament.{panel}.tenant.profile
     * (what Filament's getTenantProfileUrl expects). Only register when we're
     * inside the tenant name group; when called from the pages loop we skip
     * so we don't create a duplicate filament.{panel}.profile route.
     */
    public static function registerRoutes(Panel $panel): void
    {
        if (! static::isInTenantRouteGroup()) {
            return;
        }
        static::routes($panel);
    }

    /**
     * Whether the current route group stack is inside the tenant. name group.
     */
    protected static function isInTenantRouteGroup(): bool
    {
        $router = app(Router::class);
        $stack = $router->getGroupStack();
        $namePrefix = '';
        foreach ($stack as $group) {
            $namePrefix .= $group['as'] ?? '';
        }

        return str_ends_with($namePrefix, 'tenant.');
    }

    public static function getLabel(): string
    {
        return __('filament-tenancy::messages.Team Profile');
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
