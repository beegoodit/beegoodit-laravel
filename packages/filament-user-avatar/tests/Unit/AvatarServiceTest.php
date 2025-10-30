<?php

use BeeGoodIT\FilamentUserAvatar\Services\AvatarService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
    $this->service = new AvatarService();
    $this->user = new class extends Model {
        public $id = 'test-uuid-123';
        public $avatar = null;
    };
});

it('stores avatar from binary data', function () {
    $imageData = 'fake image data';
    $path = $this->service->storeAvatar($this->user, $imageData, 'jpg');
    
    expect($path)->toContain('users/test-uuid-123/avatar');
    expect($path)->toContain('.jpg');
    Storage::disk('public')->assertExists($path);
});

it('stores avatar from base64', function () {
    $base64 = 'data:image/png;base64,' . base64_encode('fake png data');
    $path = $this->service->storeAvatarFromBase64($this->user, $base64);
    
    expect($path)->not->toBeNull();
    expect($path)->toContain('.png');
    Storage::disk('public')->assertExists($path);
});

it('returns null for invalid base64', function () {
    $path = $this->service->storeAvatarFromBase64($this->user, 'invalid-data');
    expect($path)->toBeNull();
});

it('deletes existing avatar', function () {
    $this->user->avatar = 'users/test/avatar.jpg';
    Storage::disk('public')->put($this->user->avatar, 'data');
    
    $this->service->deleteAvatar($this->user);
    
    Storage::disk('public')->assertMissing($this->user->avatar);
});

it('updates avatar and deletes old one', function () {
    $this->user->avatar = 'users/test/old.jpg';
    Storage::disk('public')->put($this->user->avatar, 'old');
    
    $newPath = 'users/test/new.jpg';
    Storage::disk('public')->put($newPath, 'new');
    
    $this->service->updateUserAvatar($this->user, $newPath);
    
    expect($this->user->avatar)->toBe($newPath);
    Storage::disk('public')->assertMissing('users/test/old.jpg');
});

