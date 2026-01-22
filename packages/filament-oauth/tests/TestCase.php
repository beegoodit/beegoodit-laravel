<?php

namespace BeegoodIT\FilamentOAuth\Tests;

use BeegoodIT\FilamentOAuth\FilamentOAuthServiceProvider;
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
            FilamentOAuthServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        // Set encryption key for testing encrypted columns
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }
}
