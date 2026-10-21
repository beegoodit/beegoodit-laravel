<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\FilamentAiServiceProvider;
use Illuminate\Database\Schema\Blueprint;
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
     * @param  \Illuminate\Foundation\Application  $app
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
        $app['config']->set('filament-ai.tenant_key_type', 'int');
        $app['config']->set('filament-ai.user_key_type', 'int');
        $app['config']->set('filament-ai.tenant_column', 'tenant_id');
        $app['config']->set('filament-ai.permissions.manage_settings', null);
        $app['config']->set('filament-ai.permissions.use_assistant', null);
        $app['config']->set('filament-ai.chat.streaming', false);
        $app['config']->set('filament-ai.attachments.disk', 'local');
        $app['config']->set('filesystems.disks.local.root', storage_path('app'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        \BeegoodIT\FilamentAi\Support\FilamentAi::useSettingsModel(\BeegoodIT\FilamentAi\Models\Setting::class);

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

        // Host apps add the tenant column; tests simulate a multi-tenant host.
        $conversationsTable = (string) config('ai.conversations.tables.conversations', 'agent_conversations');
        $tenantColumn = (string) config('filament-ai.tenant_column', 'tenant_id');

        if (Schema::hasTable($conversationsTable) && ! Schema::hasColumn($conversationsTable, $tenantColumn)) {
            Schema::table($conversationsTable, function (Blueprint $blueprint) use ($tenantColumn): void {
                $blueprint->unsignedBigInteger($tenantColumn)->nullable()->index();
            });
        }

        $mcpTokensMigration = require __DIR__.'/../database/migrations/2026_10_13_000003_create_filament_ai_mcp_tokens_table.php';
        $mcpTokensMigration->up();
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
