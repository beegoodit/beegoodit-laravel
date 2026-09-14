<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Actions\EvaluateStreakPeriods;
use BeegoodIT\FilamentStreaks\Actions\RecordStreakEvents;
use BeegoodIT\FilamentStreaks\Models\Notification as StreakNotification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Notifications\BrokenNotification;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\FakeStrategy;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\StreakEvent;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

afterEach(function (): void {
    FakeStrategy::reset();
    config(['filament-streaks.strategies.test_strategy.breaks.notifications' => false]);
});

it('breaks a streak after a quiet week past grace', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'quiet-week@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-03-09',
    ]);

    $asOf = Carbon::parse('2026-03-25 12:00:00', 'UTC');

    expect(EvaluateStreakPeriods::run($asOf))->toBe(1)
        ->and($streak->fresh()->isOpen())->toBeFalse()
        ->and($streak->fresh()->end_at?->toDateString())->toBe('2026-03-15');
});

it('does not break when a qualification is recorded during grace', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'grace-qual@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-03-09',
    ]);

    StreakEvent::query()->create([
        'name' => 'Play',
        'occurred_at' => Carbon::parse('2026-03-12 18:00:00', 'UTC'),
    ]);

    RecordStreakEvents::run('test_strategy', $user);

    $asOf = Carbon::parse('2026-03-16 12:00:00', 'UTC');

    expect(EvaluateStreakPeriods::run($asOf))->toBe(0)
        ->and($streak->fresh()->isOpen())->toBeTrue();
});

it('silently breaks when past activeTo with when_inactive break', function (): void {
    FakeStrategy::$activeTo = Carbon::parse('2026-01-01', 'UTC');

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'inactive@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2025-12-01',
    ]);

    $asOf = Carbon::parse('2026-06-01 12:00:00', 'UTC');

    expect(EvaluateStreakPeriods::run($asOf))->toBe(1)
        ->and($streak->fresh()->isOpen())->toBeFalse()
        ->and(StreakNotification::query()->where('kind', StreakNotification::KIND_BROKEN)->count())->toBe(0);
});

it('logs a broken notification when breaks notifications are enabled', function (): void {
    config(['filament-streaks.strategies.test_strategy.breaks.notifications' => true]);

    Notification::fake();

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'broken-notify@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-03-09',
    ]);

    $asOf = Carbon::parse('2026-03-25 12:00:00', 'UTC');

    EvaluateStreakPeriods::run($asOf);

    expect(StreakNotification::query()
        ->where('streak_id', $streak->getKey())
        ->where('kind', StreakNotification::KIND_BROKEN)
        ->count())->toBe(1);

    Notification::assertSentTo($user, BrokenNotification::class);
});
