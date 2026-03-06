<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedCreateForm;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class FeedCreateFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_create_item_creates_feed_item_with_attachments_and_redirects(): void
    {
        Storage::fake('public');

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('photo.jpg');

        Livewire::test(FeedCreateForm::class, ['entity' => $user])
            ->set('subject', 'Test subject')
            ->set('body', '<p>Test body</p>')
            ->set('attachments', [$file])
            ->call('createItem')
            ->assertRedirect();

        $this->assertCount(1, FeedItem::all());
        $item = FeedItem::first();
        $this->assertSame('Test subject', $item->subject);
        $this->assertSame('<p>Test body</p>', $item->body);
        $this->assertIsArray($item->attachments);
        $this->assertCount(1, $item->attachments);
        $this->assertStringContainsString('feed-item-attachments', $item->attachments[0]);
        Storage::disk('public')->assertExists($item->attachments[0]);
    }

    public function test_feed_create_form_renders_drop_placeholder(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(FeedCreateForm::class, ['entity' => $user])
            ->assertSee(__('filament-social-graph::feed_item.attachments_drop_placeholder'), false);
    }
}
