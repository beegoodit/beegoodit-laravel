<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\DeleteFeedItem;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Storage;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('filament-social-graph.tenancy.enabled', false);
    config()->set('filament-social-graph.entity_models', [TestUser::class]);
});

test('it deletes feed item', function (): void {
    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'Test',
    ]);

    $id = $feedItem->id;

    DeleteFeedItem::run($feedItem);

    expect(FeedItem::find($id))->toBeNull();
});

test('it deletes stored attachment files when deleting feed item', function (): void {
    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $path = 'feed-item-attachments/test-file.pdf';
    Storage::disk('public')->put($path, 'content');

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'With attachment',
        'attachments' => [$path],
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    DeleteFeedItem::run($feedItem);

    expect(FeedItem::find($feedItem->id))->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

test('direct model delete removes stored attachment files via observer', function (): void {
    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $path = 'feed-item-attachments/direct-delete.pdf';
    Storage::disk('public')->put($path, 'content');

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'Direct delete test',
        'attachments' => [$path],
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $feedItem->delete();

    expect(FeedItem::find($feedItem->id))->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});
