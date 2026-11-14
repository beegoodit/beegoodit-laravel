<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\FilamentAiServiceProvider;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Tests\Fixtures\SubjectOwner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            FilamentAiServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('logging.default', 'errorlog');
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('filament-ai.user_key_type', 'int');
        $app['config']->set('filament-ai.tenant_column', 'site_id');
        $app['config']->set('filament-ai.subject_model', SubjectOwner::class);
        $app['config']->set('filament-ai.permissions.manage_settings', null);
        $app['config']->set('filament-ai.permissions.use_assistant', null);
        $app['config']->set('filament-ai.chat.streaming', false);
        $app['config']->set('filament-ai.attachments.disk', 'local');
        $app['config']->set('filesystems.disks.local.root', storage_path('app'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        FilamentAi::useSettingsModel(Setting::class);

        Schema::create('filament_ai_subject_owners', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        $aiMigration = require __DIR__.'/../vendor/laravel/ai/database/migrations/2026_01_11_000001_create_agent_conversations_table.php';
        $aiMigration->up();

        $settingsMigration = require __DIR__.'/../database/migrations/2026_10_12_000001_create_filament_ai_settings_table.php';
        $settingsMigration->up();

        $conversationsMigration = require __DIR__.'/../database/migrations/2026_10_12_000002_add_mode_to_agent_conversations_table.php';
        $conversationsMigration->up();

        $mcpTokensMigration = require __DIR__.'/../database/migrations/2026_10_13_000003_create_filament_ai_mcp_tokens_table.php';
        $mcpTokensMigration->up();

        $subjectMigration = require __DIR__.'/../database/migrations/2026_11_09_000005_replace_filament_ai_tenant_column_with_subject.php';
        $subjectMigration->up();
    }

    protected function subject(string $id): SubjectOwner
    {
        return SubjectOwner::query()->firstOrCreate([
            'id' => $id,
        ]);
    }

    protected function actingAsUser(): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        return $user;
    }
}

class User extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];
}
