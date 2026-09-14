<?php

namespace BeegoodIT\FilamentLegalEntity\Tests;

use BeegoodIT\FilamentLegalEntity\Enums\LegalForm;
use BeegoodIT\FilamentLegalEntity\Models\LegalEntity;
use Illuminate\Validation\ValidationException;

class LegalEntityTest extends TestCase
{
    public function test_it_creates_an_entity_for_an_owner_via_the_trait(): void
    {
        $owner = UserWithLegalEntity::query()->create([
            'name' => 'Team Owner',
            'email' => 'team@example.com',
            'password' => 'password',
        ]);

        $owner->legalEntity()->create([
            'name' => 'BeegoodIT GmbH',
            'address_line' => 'Example Street 1',
            'postal_code' => '10115',
            'city' => 'Berlin',
            'email' => 'info@beegoodit.de',
            'country' => 'DE',
            'profile' => [
                'legal_form' => LegalForm::Gmbh->value,
                'representative' => 'Jane Doe',
                'register_court' => 'Amtsgericht Berlin',
                'register_number' => 'HRB 123456',
            ],
        ]);

        $entity = $owner->refresh()->legalEntity;

        $this->assertNotNull($entity);
        $this->assertSame('BeegoodIT GmbH', $entity->name);
        $this->assertSame((string) $owner->id, $entity->owner_id);
        $this->assertSame($owner->getMorphClass(), $entity->owner_type);
        $this->assertSame('Jane Doe', $entity->profileValue('representative'));
        $this->assertSame(['Example Street 1', '10115 Berlin'], $entity->addressLines());
    }

    public function test_owner_id_stores_integer_morph_keys_as_strings(): void
    {
        $entity = LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => 3,
        ]);

        $this->assertSame('3', $entity->owner_id);
    }

    public function test_owner_id_accepts_uuids(): void
    {
        $uuid = '9c1e8a4a-4c0a-4f3e-9d6d-2c1b0a9f8e7d';

        $entity = LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => $uuid,
        ]);

        $this->assertSame($uuid, $entity->owner_id);
    }

    public function test_only_one_entity_per_owner(): void
    {
        LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '1',
        ]);

        $this->expectException(ValidationException::class);

        LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '1',
        ]);
    }

    public function test_only_one_platform_entity(): void
    {
        LegalEntity::factory()->platform()->create();

        $this->expectException(ValidationException::class);

        LegalEntity::factory()->platform()->create();
    }

    public function test_vat_id_is_not_unique(): void
    {
        LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '1',
            'profile' => [
                'legal_form' => LegalForm::Gmbh->value,
                'representative' => 'A',
                'register_court' => 'Amtsgericht Berlin',
                'register_number' => 'HRB 1',
                'vat_id' => 'DE123456789',
            ],
        ]);

        $second = LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '2',
            'profile' => [
                'legal_form' => LegalForm::Gmbh->value,
                'representative' => 'B',
                'register_court' => 'Amtsgericht Köln',
                'register_number' => 'HRB 2',
                'vat_id' => 'DE123456789',
            ],
        ]);

        $this->assertSame('DE123456789', $second->profileValue('vat_id'));
    }

    public function test_hard_delete_frees_the_owner_slot(): void
    {
        $entity = LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '1',
        ]);

        $entity->delete();

        $this->assertNull(LegalEntity::query()->where('owner_id', '1')->first());

        $replacement = LegalEntity::factory()->create([
            'owner_type' => UserWithLegalEntity::class,
            'owner_id' => '1',
        ]);

        $this->assertNotSame($entity->id, $replacement->id);
    }

    public function test_country_is_required(): void
    {
        $this->expectException(ValidationException::class);

        LegalEntity::factory()->create([
            'country' => '',
        ]);
    }

    public function test_generic_country_discards_profile_extras(): void
    {
        $entity = LegalEntity::factory()->generic()->create([
            'profile' => [
                'legal_form' => LegalForm::Gmbh->value,
                'representative' => 'Should go',
            ],
        ]);

        $this->assertSame([], $entity->profile);
        $this->assertSame('California', $entity->region);
        $this->assertContains('California', $entity->addressLines());
    }

    public function test_de_clears_region(): void
    {
        $entity = LegalEntity::factory()->create([
            'address_line' => 'Example Street 1',
            'postal_code' => '10115',
            'city' => 'Berlin',
            'region' => 'Bayern',
        ]);

        $this->assertNull($entity->region);
        $this->assertSame(['Example Street 1', '10115 Berlin'], $entity->addressLines());
    }
}
