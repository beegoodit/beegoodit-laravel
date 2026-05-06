<?php

namespace BeegoodIT\EloquentReadonlyAttributes\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('readonly_test_models', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->string('description')->nullable();
            $table->boolean('locked')->default(false);
            $table->boolean('readonly_name')->default(false);
            $table->timestamps();
        });
    }
}
