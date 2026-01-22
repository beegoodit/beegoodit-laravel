<?php

namespace BeegoodIT\FilamentUserAvatar\Tests\Unit;

use BeegoodIT\FilamentUserAvatar\Services\AvatarService;
use BeegoodIT\FilamentUserAvatar\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AvatarServicePhpUnitTest extends TestCase
{
    protected AvatarService $service;

    protected Model $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('s3');
        $this->service = new AvatarService;

        $this->user = new class extends Model
        {
            public $id = 'test-uuid-123';

            public $avatar;
        };
    }

    public function test_it_stores_avatar_from_binary_data(): void
    {
        $imageData = 'fake image data';
        $path = $this->service->storeAvatar($this->user, $imageData, 'jpg');

        $this->assertStringContainsString('users/test-uuid-123/avatar', $path);
        $this->assertStringContainsString('.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_stores_avatar_from_base64(): void
    {
        $base64 = 'data:image/png;base64,'.base64_encode('fake png data');
        $path = $this->service->storeAvatarFromBase64($this->user, $base64);

        $this->assertNotNull($path);
        $this->assertStringContainsString('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_returns_null_for_invalid_base64(): void
    {
        $path = $this->service->storeAvatarFromBase64($this->user, 'invalid-data');
        $this->assertNull($path);
    }

    public function test_it_deletes_existing_avatar(): void
    {
        $this->user->avatar = 'users/test/avatar.jpg';
        Storage::disk('public')->put($this->user->avatar, 'data');

        $this->service->deleteAvatar($this->user);

        Storage::disk('public')->assertMissing($this->user->avatar);
    }
}
