<?php

namespace BeegoodIT\FilamentTenancyRoles\Tests\Unit;

use BeegoodIT\FilamentTenancyRoles\Enums\TeamRole;
use BeegoodIT\FilamentTenancyRoles\Tests\TestCase;

class TeamRoleTest extends TestCase
{
    public function test_it_has_expected_enum_values(): void
    {
        $this->assertSame('owner', TeamRole::Owner->value);
        $this->assertSame('admin', TeamRole::Admin->value);
        $this->assertSame('member', TeamRole::Member->value);
    }

    public function test_it_can_return_role_labels(): void
    {
        $this->assertSame('Owner', TeamRole::Owner->label());
        $this->assertSame('Admin', TeamRole::Admin->label());
        $this->assertSame('Member', TeamRole::Member->label());
    }
}
