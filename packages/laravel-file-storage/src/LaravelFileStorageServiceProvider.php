<?php

namespace BeeGoodIT\LaravelFileStorage;

use Illuminate\Support\ServiceProvider;

class LaravelFileStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\BeeGoodIT\LaravelFileStorage\Services\FileStorageService::class);
    }
}

