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
        config()->set('cache.default', 'array');
        config()->set('queue.default', 'sync');
        config()->set('auth.providers.users.model', TestUser::class);
        config()->set('filament-social-graph.tenancy.enabled', false);
        config()->set('filament-social-graph.owner_models', [TestUser::class]);
        config()->set('filament-social-graph.subscription_rule_scopes', [
            'all_users' => 'All users',
            'team_members' => 'Team members',
        ]);
        config()->set('filament-social-graph.entity_models', [TestTeam::class]);
    }

    /**
     * Minimal valid JPEG bytes for thumbnail tests. Requires Intervention Image (GD or Imagick).
     */
    protected function minimalJpeg(): string
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            $this->markTestSkipped('Intervention Image not installed');
        }
        $driver = extension_loaded('gd') ? new \Intervention\Image\Drivers\Gd\Driver : new \Intervention\Image\Drivers\Imagick\Driver;
        $manager = new \Intervention\Image\ImageManager($driver);
        $image = $manager->create(10, 10)->fill('ccc');

        return (string) $image->encodeByExtension('jpg', quality: 85);
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

        Schema::create('teams', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $migrations = [
            __DIR__.'/../database/migrations/2026_02_27_000000_create_feeds_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000001_create_feed_items_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000002_create_feed_interactions_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000003_create_feed_subscription_rules_table.php',
            __DIR__.'/../database/migrations/2026_02_27_000004_create_feed_subscriptions_table.php',
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
