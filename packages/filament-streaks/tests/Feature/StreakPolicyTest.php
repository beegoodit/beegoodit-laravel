<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Policies\StreakPolicy;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;

it('allows admins full access to streaks', function (): void {
    $admin = User::query()->create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
        'is_admin' => true,
    ]);

    $player = User::query()->create([
        'name' => 'Player',
        'email' => 'player@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
        'is_admin' => false,
    ]);

    $streak = Streak::factory()->create();
    $policy = new StreakPolicy;

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $streak))->toBeTrue()
        ->and($policy->viewAny($player))->toBeFalse()
        ->and($policy->create($player))->toBeFalse();
});
