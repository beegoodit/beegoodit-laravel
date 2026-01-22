<?php

namespace BeegoodIT\FilamentLegal\Tests;

use BeegoodIT\FilamentLegal\FilamentLegalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentLegalServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);
    }

    protected function setUpDatabase($app)
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $migration = include __DIR__ . '/../database/migrations/2025_12_30_000001_create_legal_policies_table.php';
        $migration->up();

        $migration = include __DIR__ . '/../database/migrations/2025_12_30_000002_create_policy_acceptances_table.php';
        $migration->up();
    }
}

class User extends \Illuminate\Foundation\Auth\User
{
    protected $table = 'users';
    protected $guarded = [];
}
