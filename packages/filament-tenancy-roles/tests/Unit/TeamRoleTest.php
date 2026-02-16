<?php

namespace BeegoodIT\FilamentTenancyRoles\Tests\Unit;

use BeegoodIT\FilamentTenancyRoles\Enums\TeamRole;

test('it can return role labels', function (): void {
    expect(TeamRole::Owner->label())->toBe('Owner')
        ->and(TeamRole::Admin->label())->toBe('Admin')
        ->and(TeamRole::Member->label())->toBe('Member');
});
