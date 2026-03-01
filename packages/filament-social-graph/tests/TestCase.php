<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\EloquentUserstamps\EloquentUserstampsServiceProvider;
use BeegoodIT\FilamentSocialGraph\FilamentSocialGraphServiceProvider;
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

    protected function getPackageProviders($app): array
    {
        return [
            EloquentUserstampsServiceProvider::class,
            FilamentSocialGraphServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', TestUser::class);
        config()->set('filament-social-graph.tenancy.enabled', false);
        config()->set('filament-social-graph.actor_models', [TestUser::class]);
    }

    protected function setUpDatabase(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        $migrations = [
            __DIR__.'/../database/migrations/2026_02_27_000000_create_feed_items_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000001_create_subscriptions_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000002_create_feed_item_attachments_table.php',
        ];

        foreach ($migrations as $path) {
            $migration = include $path;
            $migration->up();
        }
    }
}

class TestUser extends \Illuminate\Foundation\Auth\User
{
    use \BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
    use \BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialSubscriptions;
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'users';

    protected $guarded = [];
}

class TestTeam extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'teams';

    protected $guarded = [];

    protected $fillable = ['name'];
}
