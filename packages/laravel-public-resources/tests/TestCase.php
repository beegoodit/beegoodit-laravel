<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->bound('db')) {
            Model::setConnectionResolver($this->app['db']);
            Model::setEventDispatcher($this->app['events']);
        }
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
        Schema::create('public_id_models', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->string('public_id', 8)->nullable();
            $table->timestamps();
        });
    }
}
