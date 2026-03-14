<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\CreateFeedItemForEntity;
use BeegoodIT\FilamentSocialGraph\Jobs\GenerateFeedItemThumbnailsJob;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('filament-social-graph.tenancy.enabled', false);
    config()->set('filament-social-graph.entity_models', [TestUser::class]);
});

test('it creates a feed item for an entity that uses HasSocialFeed', function (): void {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $action = new CreateFeedItemForEntity;
    $data = [
        'body' => 'Hello world',
        'subject' => 'Greeting',
    ];

    $feedItem = $action($user, $data);

    expect($feedItem)->toBeInstanceOf(FeedItem::class)
        ->and($feedItem->feed_id)->not->toBeNull()
        ->and($feedItem->body)->toBe('Hello world')
        ->and($feedItem->subject)->toBe('Greeting');
    $feed = $feedItem->feed;
    expect($feed)->not->toBeNull()
        ->and($feed->owner_type)->toBe(TestUser::class)
        ->and($feed->owner_id)->toBe($user->getKey());
    $this->assertDatabaseHas('feed_items', [
        'id' => $feedItem->id,
        'feed_id' => $feedItem->feed_id,
        'body' => 'Hello world',
    ]);
});

test('it creates feed item with attachments and stores files', function (): void {
    Storage::fake('public');
    Queue::fake();

    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');

    $action = new CreateFeedItemForEntity;
    $data = [
        'body' => 'With photo',
        'attachments' => [$file],
    ];

    $feedItem = $action($user, $data);

    expect($feedItem->attachments)->toBeArray()
        ->and($feedItem->attachments)->toHaveCount(1);

    $path = $feedItem->attachments[0];
    expect($path)->toContain('feed-item-attachments')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();

    Queue::assertPushed(GenerateFeedItemThumbnailsJob::class);
    $job = new GenerateFeedItemThumbnailsJob($feedItem->getKey());
    $job->handle(app(\BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService::class));

    $thumbPath = FeedItem::getThumbnailPath($path);
    expect(Storage::disk('public')->exists($thumbPath))->toBeTrue();
});

test('it throws when entity does not use HasSocialFeed', function (): void {
    $team = TestTeam::create(['name' => 'Test Team']);

    $action = new CreateFeedItemForEntity;
    $data = ['body' => 'Test'];

    $action($team, $data);
})->throws(\InvalidArgumentException::class);
