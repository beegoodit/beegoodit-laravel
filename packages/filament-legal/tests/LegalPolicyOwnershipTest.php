<?php

namespace BeegoodIT\FilamentLegal\Tests;

use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use Illuminate\Database\Eloquent\Model;

class LegalPolicyOwnershipTest extends TestCase
{
    public function test_it_can_retrieve_active_policy_for_platform(): void
    {
        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.0',
            'content' => ['en' => 'Platform Privacy Policy'],
            'is_active' => true,
        ]);

        $policy = LegalPolicy::getActive('privacy');

        $this->assertNotNull($policy);
        $this->assertEquals('Platform Privacy Policy', $policy->content['en']);
        $this->assertNull($policy->owner_id);
    }

    public function test_it_can_retrieve_active_policy_for_an_owner(): void
    {
        $owner = User::create([
            'name' => 'Team Owner',
            'email' => 'team@example.com',
            'password' => 'password',
        ]);

        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.0',
            'content' => ['en' => 'Team Privacy Policy'],
            'is_active' => true,
            'owner_id' => $owner->id,
            'owner_type' => $owner->getMorphClass(),
        ]);

        // Platform policy
        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.1',
            'content' => ['en' => 'Platform Privacy Policy'],
            'is_active' => true,
        ]);

        $policy = LegalPolicy::getActive('privacy', $owner);

        $this->assertNotNull($policy);
        $this->assertEquals('Team Privacy Policy', $policy->content['en']);
        $this->assertEquals($owner->id, $policy->owner_id);
    }

    public function test_it_respects_versioning_within_owner_scope(): void
    {
        $owner = User::create([
            'name' => 'Team Owner',
            'email' => 'team@example.com',
            'password' => 'password',
        ]);

        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.0',
            'content' => ['en' => 'Team Privacy Policy V1'],
            'is_active' => true,
            'owner_id' => $owner->id,
            'owner_type' => $owner->getMorphClass(),
        ]);

        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.1',
            'content' => ['en' => 'Team Privacy Policy V1.1'],
            'is_active' => true,
            'owner_id' => $owner->id,
            'owner_type' => $owner->getMorphClass(),
        ]);

        $policy = LegalPolicy::getActive('privacy', $owner);

        $this->assertNotNull($policy);
        $this->assertEquals('Team Privacy Policy V1.1', $policy->content['en']);
    }
}
