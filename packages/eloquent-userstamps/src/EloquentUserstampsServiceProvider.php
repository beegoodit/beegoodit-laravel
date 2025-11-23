<?php

namespace BeeGoodIT\EloquentUserstamps;

use Illuminate\Support\ServiceProvider;

class EloquentUserstampsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish migration stub
        $this->publishes([
            __DIR__.'/../database/migrations/add_userstamps_columns.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_userstamps_columns.php'),
        ], 'eloquent-userstamps-migrations');
    }
}
