<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentStreaks\Tests;

use BeegoodIT\FilamentStreaks\FilamentStreaksServiceProvider;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\FakeStrategy;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        RefreshDatabaseState::$migrated = false;

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActionServiceProvider::class,
            FilamentStreaksServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('filament-streaks.user_model', User::class);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('filament-streaks.strategies', [
            'test_strategy' => [
                'strategy' => FakeStrategy::class,
                'open_on' => 'first_event',
                'active_from' => null,
                'active_to' => null,
                'when_inactive' => 'break',
                'at_risk' => [
                    'notifications' => false,
                    'lead' => '24h',
                ],
                'breaks' => [
                    'grace_period' => '2d',
                    'notifications' => false,
                    'notification_delay' => '5m',
                ],
                'extended' => [
                    'notifications' => false,
                ],
            ],
        ]);
    }
}
