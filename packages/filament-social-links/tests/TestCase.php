<?php

namespace BeegoodIT\FilamentSocialLinks\Tests;

use BeegoodIT\EloquentUserstamps\EloquentUserstampsServiceProvider;
use BeegoodIT\FilamentSocialLinks\FilamentSocialLinksServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            EloquentUserstampsServiceProvider::class,
            FilamentSocialLinksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('test_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/2026_01_29_000000_create_social_links_tables.php';
        $migration->up();
    }
}

class User extends \Illuminate\Foundation\Auth\User
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'users';

    protected $guarded = [];
}

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use \BeegoodIT\FilamentSocialLinks\Models\Concerns\HasSocialLinks;
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];
}
