<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\UpdateFeedItem;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set('filament-social-graph.tenancy.enabled', false);
    config()->set('filament-social-graph.entity_models', [TestUser::class]);
});

test('it updates feed item subject and body', function (): void {
    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'subject' => 'Old subject',
        'body' => 'Old body',
    ]);

    UpdateFeedItem::run($feedItem, [
        'subject' => 'New subject',
        'body' => 'New body',
    ]);

    $feedItem->refresh();

    expect($feedItem->subject)->toBe('New subject')
        ->and($feedItem->body)->toBe('New body');
});

test('it updates only provided fields', function (): void {
    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'subject' => 'Keep subject',
        'body' => 'Old body',
    ]);

    UpdateFeedItem::run($feedItem, [
        'body' => 'New body',
    ]);

    $feedItem->refresh();

    expect($feedItem->subject)->toBe('Keep subject')
        ->and($feedItem->body)->toBe('New body');
});

test('it removes attachments and deletes files from storage when attachments_remove provided', function (): void {
    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $pathToRemove = 'feed-item-attachments/remove-me.pdf';
    $pathToKeep = 'feed-item-attachments/keep-me.pdf';
    Storage::disk('public')->put($pathToRemove, 'content');
    Storage::disk('public')->put($pathToKeep, 'content');

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'With attachments',
        'attachments' => [$pathToRemove, $pathToKeep],
    ]);

    UpdateFeedItem::run($feedItem, [
        'subject' => $feedItem->subject,
        'body' => $feedItem->body,
        'attachments_remove' => [$pathToRemove],
    ]);

    $feedItem->refresh();

    expect($feedItem->attachments)->toEqual([$pathToKeep])
        ->and(Storage::disk('public')->exists($pathToRemove))->toBeFalse()
        ->and(Storage::disk('public')->exists($pathToKeep))->toBeTrue();
});

test('it deletes thumbnail when removing image attachment', function (): void {
    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $imagePath = 'feed-item-attachments/remove-me.jpg';
    $thumbPath = FeedItem::getThumbnailPath($imagePath);
    Storage::disk('public')->put($imagePath, 'image content');
    Storage::disk('public')->put($thumbPath, 'thumb content');

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'With image',
        'attachments' => [$imagePath],
    ]);

    UpdateFeedItem::run($feedItem, [
        'subject' => $feedItem->subject,
        'body' => $feedItem->body,
        'attachments_remove' => [$imagePath],
    ]);

    expect(Storage::disk('public')->exists($imagePath))->toBeFalse()
        ->and(Storage::disk('public')->exists($thumbPath))->toBeFalse();
});

test('it adds new attachment files when attachments provided', function (): void {
    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $existingPath = 'feed-item-attachments/existing.jpg';
    Storage::disk('public')->put($existingPath, 'content');

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'With one attachment',
        'attachments' => [$existingPath],
    ]);

    $newFile = UploadedFile::fake()->image('new-photo.jpg');

    UpdateFeedItem::run($feedItem, [
        'subject' => $feedItem->subject,
        'body' => $feedItem->body,
        'attachments' => [$newFile],
    ]);

    $feedItem->refresh();

    expect($feedItem->attachments)->toHaveCount(2)
        ->and($feedItem->attachments[0])->toBe($existingPath);
    expect(Storage::disk('public')->exists($feedItem->attachments[1]))->toBeTrue();

    $newPath = $feedItem->attachments[1];
    $thumbPath = FeedItem::getThumbnailPath($newPath);
    expect(Storage::disk('public')->exists($thumbPath))->toBeTrue();
});

test('it throws validation exception when combined attachments exceed max_files', function (): void {
    config()->set('filament-social-graph.attachments.max_files', 2);

    Storage::fake('public');

    $actor = TestUser::create([
        'name' => 'Actor',
        'email' => 'actor@example.com',
        'password' => bcrypt('password'),
    ]);

    $feed = Feed::firstOrCreateForOwner($actor);
    $feedItem = FeedItem::create([
        'feed_id' => $feed->getKey(),
        'body' => 'Body',
        'attachments' => ['feed-item-attachments/a.pdf', 'feed-item-attachments/b.pdf'],
    ]);

    $newFile = UploadedFile::fake()->image('extra.jpg');

    UpdateFeedItem::run($feedItem, [
        'subject' => $feedItem->subject,
        'body' => $feedItem->body,
        'attachments' => [$newFile],
    ]);
})->throws(ValidationException::class);
