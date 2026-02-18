<?php

namespace BeegoodIT\LaravelFeedback\Tests;

use BeegoodIT\LaravelFeedback\FeedbackServiceProvider;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FeedbackServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set user model to UUID-capable test user (table uses uuid primary key)
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('feedback.user_model', TestUser::class);

        // Create users table for testing (with UUIDs)
        Schema::create('users', function ($table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Run feedback items migration (no RefreshDatabase to avoid duplicate run)
        $migration = include __DIR__.'/../database/migrations/2026_02_04_155744_create_feedback_items_table.php';
        $migration->up();
    }

    protected function createUser(array $attributes = []): TestUser
    {
        return TestUser::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }
}

class TestUser extends Authenticatable
{
    use HasUuids;

    protected $table = 'users';

    protected $guarded = [];
}
