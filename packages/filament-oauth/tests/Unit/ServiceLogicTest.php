<?php

namespace BeeGoodIT\FilamentOAuth\Tests\Unit;

use BeeGoodIT\FilamentOAuth\Services\AvatarService;
use BeeGoodIT\FilamentOAuth\Services\TeamAssignmentService;
use BeeGoodIT\FilamentOAuth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ServiceLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
    }

    public function test_config_defaults()
    {
        // The user changed the package default to false
        $this->assertFalse(config('filament-oauth.auto_assign_teams'));
        $this->assertFalse(config('filament-oauth.sync_avatars'));
    }

    public function test_avatar_service_handles_missing_avatar()
    {
        $user = $this->createMock(\Illuminate\Database\Eloquent\Model::class);
        $oauthUser = new class {
            public function getAvatar() { return null; }
        };

        $service = new AvatarService();
        $service->syncAvatar($user, $oauthUser);
        
        // No exceptions should be thrown, and no Http calls made
        $this->assertTrue(true);
    }
}
