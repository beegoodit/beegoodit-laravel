<?php

namespace BeeGoodIT\FilamentUserAvatar;

use Illuminate\Support\ServiceProvider;

class FilamentUserAvatarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\BeeGoodIT\FilamentUserAvatar\Services\AvatarService::class);
    }

    public function boot(): void
    {
        // Publish migration
        $this->publishes([
            __DIR__.'/../database/migrations/add_avatar_field.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_avatar_to_users_table.php'),
        ], 'user-avatar-migrations');
    }
}

