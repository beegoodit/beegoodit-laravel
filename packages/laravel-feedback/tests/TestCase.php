<?php

namespace BeegoodIT\LaravelFeedback\Tests;

use BeegoodIT\LaravelFeedback\FeedbackServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FeedbackServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set user model
        $app['config']->set('feedback.user_model', \Illuminate\Foundation\Auth\User::class);

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

        // Run feedback items migration
        $migration = include __DIR__.'/../database/migrations/2026_02_04_155744_create_feedback_items_table.php';
        $migration->up();
    }
}
