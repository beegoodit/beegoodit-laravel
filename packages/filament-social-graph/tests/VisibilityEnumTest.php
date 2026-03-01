<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;

class VisibilityEnumTest extends TestCase
{
    public function test_visibility_enum_has_expected_cases(): void
    {
        $cases = Visibility::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(Visibility::Public, $cases);
        $this->assertContains(Visibility::Unlisted, $cases);
        $this->assertContains(Visibility::Private, $cases);
        $this->assertContains(Visibility::Followers, $cases);
    }

    public function test_visibility_enum_values(): void
    {
        $this->assertEquals('public', Visibility::Public->value);
        $this->assertEquals('unlisted', Visibility::Unlisted->value);
        $this->assertEquals('private', Visibility::Private->value);
        $this->assertEquals('followers', Visibility::Followers->value);
    }

    public function test_visibility_enum_has_labels(): void
    {
        $this->assertNotEmpty(Visibility::Public->label());
    }
}
