<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\CreateFeedItemForEntity;
use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;

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
        'visibility' => Visibility::Public,
    ];

    $feedItem = $action($user, $data);

    expect($feedItem)->toBeInstanceOf(FeedItem::class)
        ->and($feedItem->actor_type)->toBe(TestUser::class)
        ->and($feedItem->actor_id)->toBe($user->getKey())
        ->and($feedItem->body)->toBe('Hello world')
        ->and($feedItem->subject)->toBe('Greeting')
        ->and($feedItem->visibility)->toBe(Visibility::Public);
    $this->assertDatabaseHas('feed_items', [
        'id' => $feedItem->id,
        'actor_type' => TestUser::class,
        'actor_id' => $user->id,
        'body' => 'Hello world',
    ]);
});

test('it throws when entity does not use HasSocialFeed', function (): void {
    $team = TestTeam::create(['name' => 'Test Team']);

    $action = new CreateFeedItemForEntity;
    $data = ['body' => 'Test', 'visibility' => Visibility::Public];

    $action($team, $data);
})->throws(\InvalidArgumentException::class);
