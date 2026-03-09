<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedCreateForm;
use Livewire\Livewire;

class FeedCreateFormUploadModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_renders_native_multiple_input_when_mode_is_native(): void
    {
        config()->set('filament-social-graph.attachments.multiple_upload_mode', 'native');

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(FeedCreateForm::class, ['entity' => $user]);
        $html = $component->html();

        $this->assertStringContainsString('data-feed-drop-zone="feed-composer-attachments"', $html);
        $this->assertStringContainsString('id="feed-composer-attachments"', $html);
        $this->assertStringContainsString('wire:model="attachments"', $html);
        $this->assertMatchesRegularExpression('/<input\s(?=[^>]*id="feed-composer-attachments")(?=[^>]*\bmultiple\b)[^>]*>/', $html);
    }

    public function test_renders_single_per_request_workaround_when_mode_is_single_per_request(): void
    {
        config()->set('filament-social-graph.attachments.multiple_upload_mode', 'single_per_request');

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(FeedCreateForm::class, ['entity' => $user]);
        $html = $component->html();

        $this->assertStringContainsString('data-feed-drop-zone-single', $html);
        $this->assertStringNotContainsString('wire:model="attachments"', $html);
        $this->assertDoesNotMatchRegularExpression('/<input\s(?=[^>]*id="feed-composer-attachments")(?=[^>]*\bmultiple\b)[^>]*>/', $html);
    }

    public function test_renders_native_multiple_input_when_mode_is_auto_and_temp_disk_is_local(): void
    {
        config()->set('filament-social-graph.attachments.multiple_upload_mode', 'auto');
        config()->set('livewire.temporary_file_upload.disk', null);
        config()->set('filesystems.default', 'local');

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(FeedCreateForm::class, ['entity' => $user]);
        $html = $component->html();

        $this->assertStringContainsString('data-feed-drop-zone="feed-composer-attachments"', $html);
        $this->assertMatchesRegularExpression('/<input\s(?=[^>]*id="feed-composer-attachments")(?=[^>]*\bmultiple\b)[^>]*>/', $html);
    }

    public function test_renders_single_per_request_workaround_when_mode_is_auto_and_temp_disk_is_s3(): void
    {
        config()->set('filament-social-graph.attachments.multiple_upload_mode', 'auto');
        config()->set('livewire.temporary_file_upload.disk', 's3');
        config()->set('filesystems.disks.s3', [
            'driver' => 's3',
            'key' => 'test',
            'secret' => 'test',
            'region' => 'us-east-1',
            'bucket' => 'test',
        ]);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(FeedCreateForm::class, ['entity' => $user]);
        $html = $component->html();

        $this->assertStringContainsString('data-feed-drop-zone-single', $html);
        $this->assertStringNotContainsString('wire:model="attachments"', $html);
    }
}
