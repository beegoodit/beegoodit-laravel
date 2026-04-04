<?php

namespace BeegoodIT\FilamentEntryLinks\Tests;

use BeegoodIT\FilamentEntryLinks\FilamentEntryLinksServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            FilamentEntryLinksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.url', 'https://example.com');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('filament-entry-links.allowed_url_mode', 'same_app');
        $app['config']->set('filament-entry-links.home_url', 'https://example.com/home');
    }
}
