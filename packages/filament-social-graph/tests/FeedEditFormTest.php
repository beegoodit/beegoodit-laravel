<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedEditForm;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class FeedEditFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config()->set('filament-social-graph.entity_models', [TestUser::class]);
    }

    public function test_update_item_updates_subject_body_and_adds_new_attachment(): void
    {
        Storage::fake('public');

        $user = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'subject' => 'Old subject',
            'body' => '<p>Old body</p>',
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('new-photo.jpg');

        Livewire::test(FeedEditForm::class, [
            'feedItem' => $feedItem,
            'feedUrl' => '/feed',
        ])
            ->set('subject', 'Updated subject')
            ->set('body', '<p>Updated body</p>')
            ->set('attachments', [$file])
            ->call('updateItem')
            ->assertRedirect('/feed');

        $feedItem->refresh();
        $this->assertSame('Updated subject', $feedItem->subject);
        $this->assertSame('<p>Updated body</p>', $feedItem->body);
        $this->assertIsArray($feedItem->attachments);
        $this->assertCount(1, $feedItem->attachments);
        $this->assertStringContainsString('feed-item-attachments', $feedItem->attachments[0]);
        Storage::disk('public')->assertExists($feedItem->attachments[0]);
    }

    public function test_update_item_merges_existing_attachments_with_new_ones(): void
    {
        Storage::fake('public');

        $existingPath = 'feed-item-attachments/existing.jpg';
        Storage::disk('public')->put($existingPath, 'content');

        $user = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'subject' => 'Subject',
            'body' => '<p>Body</p>',
            'attachments' => [$existingPath],
        ]);

        $this->actingAs($user);

        $newFile = UploadedFile::fake()->image('new-photo.jpg');

        Livewire::test(FeedEditForm::class, [
            'feedItem' => $feedItem,
            'feedUrl' => '/feed',
        ])
            ->set('subject', 'Subject')
            ->set('body', '<p>Body</p>')
            ->set('attachments', [$newFile])
            ->call('updateItem')
            ->assertRedirect('/feed');

        $feedItem->refresh();
        $this->assertCount(2, $feedItem->attachments);
        $this->assertSame($existingPath, $feedItem->attachments[0]);
        $this->assertStringContainsString('feed-item-attachments', $feedItem->attachments[1]);
        Storage::disk('public')->assertExists($feedItem->attachments[1]);
    }

    public function test_update_item_removes_attachment_when_marked_for_removal(): void
    {
        Storage::fake('public');

        $pathToRemove = 'feed-item-attachments/remove-me.jpg';
        $pathToKeep = 'feed-item-attachments/keep-me.jpg';
        Storage::disk('public')->put($pathToRemove, 'content');
        Storage::disk('public')->put($pathToKeep, 'content');

        $user = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'subject' => 'Subject',
            'body' => '<p>Body</p>',
            'attachments' => [$pathToRemove, $pathToKeep],
        ]);

        $this->actingAs($user);

        Livewire::test(FeedEditForm::class, [
            'feedItem' => $feedItem,
            'feedUrl' => '/feed',
        ])
            ->set('subject', 'Subject')
            ->set('body', '<p>Body</p>')
            ->call('markAttachmentForRemoval', $pathToRemove)
            ->call('updateItem')
            ->assertRedirect('/feed');

        $feedItem->refresh();
        $this->assertSame([$pathToKeep], $feedItem->attachments);
        $this->assertFalse(Storage::disk('public')->exists($pathToRemove));
        $this->assertTrue(Storage::disk('public')->exists($pathToKeep));
    }
}
