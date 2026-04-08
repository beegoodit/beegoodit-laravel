<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests;

use BeegoodIT\FilamentOpeningTimes\FilamentOpeningTimesServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            FilamentOpeningTimesServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('opening_times_test_openables', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->timestamps();
        });
    }
}
