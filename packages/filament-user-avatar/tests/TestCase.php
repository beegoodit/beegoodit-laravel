<?php

namespace BeegoodIT\FilamentUserAvatar\Tests;

use BeegoodIT\FilamentUserAvatar\FilamentUserAvatarServiceProvider;
use BeegoodIT\LaravelFileStorage\LaravelFileStorageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelFileStorageServiceProvider::class,
            FilamentUserAvatarServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }
}
