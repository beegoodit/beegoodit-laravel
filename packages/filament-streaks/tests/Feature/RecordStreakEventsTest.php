<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Actions\RecordStreakEvents;
use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\StreakEvent;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Carbon;

it('records events and opens a streak on first event', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'player@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    StreakEvent::query()->create([
        'name' => 'Play',
        'occurred_at' => Carbon::parse('2026-03-18 18:00:00', 'UTC'),
    ]);

    $streak = RecordStreakEvents::run('test_strategy', $user);

    expect($streak)->toBeInstanceOf(Streak::class)
        ->and($streak->exists)->toBeTrue()
        ->and($streak->isOpen())->toBeTrue()
        ->and(Qualification::query()->where('streak_id', $streak->getKey())->count())->toBe(1);
});

it('upserts qualifications idempotently', function (): void {
    $user = User::query()->create([
        'name' => 'Player Two',
        'email' => 'player-two@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $event = StreakEvent::query()->create([
        'name' => 'Play again',
        'occurred_at' => Carbon::parse('2026-03-18 18:00:00', 'UTC'),
    ]);

    RecordStreakEvents::run('test_strategy', $user);
    RecordStreakEvents::run('test_strategy', $user);

    expect(Qualification::query()->count())->toBe(1)
        ->and(Qualification::query()->first()->event_id)->toBe($event->getKey());
});

it('closes the open run and starts a new one when events skip a week', function (): void {
    $user = User::query()->create([
        'name' => 'Gap Player',
        'email' => 'gap-player@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    StreakEvent::query()->create([
        'name' => 'Week A',
        'occurred_at' => Carbon::parse('2026-03-10 18:00:00', 'UTC'),
    ]);
    StreakEvent::query()->create([
        'name' => 'Week C',
        'occurred_at' => Carbon::parse('2026-03-24 18:00:00', 'UTC'),
    ]);

    $open = RecordStreakEvents::run('test_strategy', $user);

    $ended = Streak::query()
        ->ended()
        ->where('subject_id', $user->getKey())
        ->first();

    expect(Streak::query()->where('subject_id', $user->getKey())->count())->toBe(2)
        ->and($ended)->not->toBeNull()
        ->and($ended->start_at->toDateString())->toBe('2026-03-09')
        ->and($ended->end_at->toDateString())->toBe('2026-03-15')
        ->and($open->isOpen())->toBeTrue()
        ->and($open->start_at->toDateString())->toBe('2026-03-23')
        ->and(Qualification::query()->where('streak_id', $ended->getKey())->count())->toBe(1)
        ->and(Qualification::query()->where('streak_id', $open->getKey())->count())->toBe(1);
});
