<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;

it('throws when creating a second open streak for the same subject strategy and scope', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'unique-open@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'strategy_identifier' => 'test_strategy',
        'scope_type' => null,
        'scope_id' => null,
    ]);

    expect(fn () => Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'strategy_identifier' => 'test_strategy',
        'scope_type' => null,
        'scope_id' => null,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
