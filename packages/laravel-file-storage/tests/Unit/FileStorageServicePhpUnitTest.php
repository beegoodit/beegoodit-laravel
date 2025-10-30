<?php

namespace BeeGoodIT\LaravelFileStorage\Tests\Unit;

use BeeGoodIT\LaravelFileStorage\Services\FileStorageService;
use BeeGoodIT\LaravelFileStorage\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class FileStorageServicePhpUnitTest extends TestCase
{
    protected FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        Storage::fake('s3');
        $this->service = new FileStorageService();
    }

    public function test_it_stores_files_with_generated_filename()
    {
        $path = $this->service->store('file contents', 'uploads');

        $this->assertStringContainsString('uploads/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_stores_files_with_custom_filename()
    {
        $path = $this->service->store('file contents', 'uploads', 'test.txt');

        $this->assertEquals('uploads/test.txt', $path);
        Storage::disk('public')->assertExists('uploads/test.txt');
    }

    public function test_it_deletes_existing_files()
    {
        $path = $this->service->store('file contents', 'uploads', 'test.txt');
        Storage::disk('public')->assertExists($path);

        $deleted = $this->service->delete($path);

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_it_returns_false_when_deleting_non_existent_files()
    {
        $deleted = $this->service->delete('non-existent.txt');

        $this->assertFalse($deleted);
    }

    public function test_it_checks_file_existence()
    {
        $path = $this->service->store('contents', 'uploads', 'exists.txt');

        $this->assertTrue($this->service->exists($path));
        $this->assertFalse($this->service->exists('non-existent.txt'));
    }

    public function test_it_generates_urls_for_existing_files()
    {
        $path = $this->service->store('contents', 'uploads', 'test.txt');

        $url = $this->service->url($path);

        $this->assertNotNull($url);
        $this->assertStringContainsString('uploads/test.txt', $url);
    }

    public function test_it_returns_null_for_non_existent_file_urls()
    {
        $url = $this->service->url('non-existent.txt');

        $this->assertNull($url);
    }
}

