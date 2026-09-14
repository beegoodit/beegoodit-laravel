<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Facades\Schema;

it('loads package migrations', function (): void {
    expect(Schema::hasTable('beegoodit_filament_streaks_streaks'))->toBeTrue()
        ->and(Schema::hasTable('beegoodit_filament_streaks_qualifications'))->toBeTrue()
        ->and(Schema::hasTable('beegoodit_filament_streaks_notifications'))->toBeTrue()
        ->and(Schema::hasTable('beegoodit_filament_streaks_operation_runs'))->toBeTrue()
        ->and(Schema::hasColumn('beegoodit_filament_streaks_streaks', 'end_at'))->toBeTrue();
});

it('creates streaks from the factory', function (): void {
    $streak = Streak::factory()->create();

    expect($streak->isOpen())->toBeTrue()
        ->and($streak->subject)->toBeInstanceOf(User::class);
});

it('scopes open and ended streaks', function (): void {
    Streak::factory()->create();
    Streak::factory()->ended()->create();

    expect(Streak::query()->open()->count())->toBe(1)
        ->and(Streak::query()->ended()->count())->toBe(1);
});

it('enforces unique qualifications per streak and event', function (): void {
    $streak = Streak::factory()->create();

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'event_type' => 'streak-events',
        'event_id' => '11111111-1111-1111-1111-111111111111',
    ]);

    expect(fn () => Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'event_type' => 'streak-events',
        'event_id' => '11111111-1111-1111-1111-111111111111',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('cascades qualifications when a streak is deleted', function (): void {
    $qualification = Qualification::factory()->create();
    $qualificationId = $qualification->getKey();

    $qualification->streak->delete();

    expect(Qualification::query()->find($qualificationId))->toBeNull();
});
