<?php

use BeeGoodIT\LaravelFileStorage\Services\FileStorageService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
    $this->service = new FileStorageService();
});

it('stores files with generated filename', function () {
    $path = $this->service->store('file contents', 'uploads');
    
    expect($path)->toContain('uploads/');
    Storage::disk('public')->assertExists($path);
});

it('stores files with custom filename', function () {
    $path = $this->service->store('file contents', 'uploads', 'test.txt');
    
    expect($path)->toBe('uploads/test.txt');
    Storage::disk('public')->assertExists('uploads/test.txt');
});

it('deletes existing files', function () {
    $path = $this->service->store('file contents', 'uploads', 'test.txt');
    Storage::disk('public')->assertExists($path);
    
    $deleted = $this->service->delete($path);
    
    expect($deleted)->toBeTrue();
    Storage::disk('public')->assertMissing($path);
});

it('returns false when deleting non-existent files', function () {
    $deleted = $this->service->delete('non-existent.txt');
    
    expect($deleted)->toBeFalse();
});

it('checks file existence', function () {
    $path = $this->service->store('contents', 'uploads', 'exists.txt');
    
    expect($this->service->exists($path))->toBeTrue();
    expect($this->service->exists('non-existent.txt'))->toBeFalse();
});

it('generates URLs for existing files', function () {
    $path = $this->service->store('contents', 'uploads', 'test.txt');
    
    $url = $this->service->url($path);
    
    expect($url)->not->toBeNull();
    expect($url)->toContain('uploads/test.txt');
});

it('returns null for non-existent file URLs', function () {
    $url = $this->service->url('non-existent.txt');
    
    expect($url)->toBeNull();
});

