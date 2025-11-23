<?php

namespace BeeGoodIT\FilamentTenancy;

use Illuminate\Support\ServiceProvider;

class FilamentTenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/add_team_branding.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_team_branding.php'),
        ], 'tenancy-migrations');
    }
}
