<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\DeleteFeedItem;
use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
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

    $feedItem = FeedItem::create([
        'actor_type' => TestUser::class,
        'actor_id' => $actor->getKey(),
        'body' => 'Test',
        'visibility' => Visibility::Public,
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

    $feedItem = FeedItem::create([
        'actor_type' => TestUser::class,
        'actor_id' => $actor->getKey(),
        'body' => 'With attachment',
        'visibility' => Visibility::Public,
        'attachments' => [$path],
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    DeleteFeedItem::run($feedItem);

    expect(FeedItem::find($feedItem->id))->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});
