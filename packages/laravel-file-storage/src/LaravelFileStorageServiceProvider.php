<?php

namespace BeegoodIT\LaravelFileStorage;

use Illuminate\Support\ServiceProvider;

class LaravelFileStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\BeegoodIT\LaravelFileStorage\Services\FileStorageService::class);
    }
}
