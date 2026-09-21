<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Tests;

use BeegoodIT\FilamentTenancyPlugins\FilamentTenancyPluginsServiceProvider;
use BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures\IncrementingTenant;
use BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures\UuidTenant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->setUpTenantTables();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FilamentTenancyPluginsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('filament-tenancy-plugins.tenant_model', UuidTenant::class);
        $app['config']->set('filament-tenancy-plugins.slug_attribute', 'slug');
    }

    protected function setUpTenantTables(): void
    {
        Schema::create('uuid_tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('incrementing_tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
    }

    protected function uuidTenant(string $slug = 'acme'): UuidTenant
    {
        return UuidTenant::query()->create([
            'name' => 'Acme',
            'slug' => $slug,
        ]);
    }

    protected function incrementingTenant(string $slug = 'globex'): IncrementingTenant
    {
        return IncrementingTenant::query()->create([
            'name' => 'Globex',
            'slug' => $slug,
        ]);
    }
}
