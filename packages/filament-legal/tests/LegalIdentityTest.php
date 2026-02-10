<?php

namespace BeegoodIT\FilamentLegal\Tests;

use BeegoodIT\FilamentLegal\Models\LegalIdentity;
use BeegoodIT\FilamentLegal\Models\Concerns\HasLegalDocuments;
use Illuminate\Database\Eloquent\Model;

class LegalIdentityTest extends TestCase
{
    public function test_it_can_create_and_retrieve_legal_identity_for_an_owner(): void
    {
        $owner = UserWithLegal::create([
            'name' => 'Team Owner',
            'email' => 'team@example.com',
            'password' => 'password',
        ]);

        $owner->legalIdentity()->create([
            'name' => 'BeegoodIT GmbH',
            'form' => 'GmbH',
            'representative' => 'John Doe',
            'email' => 'info@beegoodit.de',
            'phone' => '+49 123 456789',
            'vat_id' => 'DE123456789',
            'register_court' => 'Amtsgericht Berlin',
            'register_number' => 'HRB 123456',
        ]);

        $identity = $owner->refresh()->legalIdentity;

        $this->assertNotNull($identity);
        $this->assertEquals('BeegoodIT GmbH', $identity->name);
        $this->assertEquals($owner->id, $identity->owner_id);
        $this->assertEquals($owner->getMorphClass(), $identity->owner_type);
    }
}

class UserWithLegal extends \Illuminate\Foundation\Auth\User
{
    use HasLegalDocuments;
    protected $table = 'users';
    protected $guarded = [];
}
