<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Services\PersistSettings;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class UuidWorkspaceSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('beegoodit_filament_ai_settings');

        config([
            'filament-ai.workspace_key_type' => 'uuid',
            'filament-ai.workspace_column' => 'workspace_id',
            'filament-ai.settings_table' => 'beegoodit_filament_ai_settings',
        ]);

        $migration = require __DIR__.'/../database/migrations/2026_10_12_000001_create_filament_ai_settings_table.php';
        $migration->up();
    }

    #[Test]
    public function it_stores_uuid_workspace_keys(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        config([
            'filament-ai.workspace_resolver' => fn (): string => $uuid,
            'filament-ai.workspace_key_type' => 'uuid',
        ]);

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-uuid',
        ]);

        $this->assertSame($uuid, $settings->workspaceKey());
        $this->assertSame($uuid, $settings->fresh()->workspaceKey());
    }
}
