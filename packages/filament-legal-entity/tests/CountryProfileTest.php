<?php

namespace BeegoodIT\FilamentLegalEntity\Tests;

use BeegoodIT\FilamentLegalEntity\Enums\LegalForm;
use BeegoodIT\FilamentLegalEntity\Profiles\CountryProfileRegistry;
use BeegoodIT\FilamentLegalEntity\Profiles\DeProfile;
use Illuminate\Validation\ValidationException;

class CountryProfileTest extends TestCase
{
    public function test_de_requires_representative_and_legal_form(): void
    {
        $this->expectException(ValidationException::class);

        (new DeProfile)->normalize([
            'legal_form' => LegalForm::Gmbh->value,
        ]);
    }

    public function test_de_registered_form_requires_court_and_number(): void
    {
        $this->expectException(ValidationException::class);

        (new DeProfile)->normalize([
            'legal_form' => LegalForm::Gmbh->value,
            'representative' => 'Jane Doe',
        ]);
    }

    public function test_nev_does_not_require_register(): void
    {
        $profile = (new DeProfile)->normalize([
            'legal_form' => LegalForm::Nev->value,
            'representative' => 'Vorstand',
        ]);

        $this->assertSame(LegalForm::Nev->value, $profile['legal_form']);
        $this->assertArrayNotHasKey('register_court', $profile);
    }

    public function test_stiftung_register_is_optional(): void
    {
        $profile = (new DeProfile)->normalize([
            'legal_form' => LegalForm::Stiftung->value,
            'representative' => 'Vorstand',
        ]);

        $this->assertSame(LegalForm::Stiftung->value, $profile['legal_form']);
    }

    public function test_other_requires_legal_form_other(): void
    {
        $this->expectException(ValidationException::class);

        (new DeProfile)->normalize([
            'legal_form' => LegalForm::Other->value,
            'representative' => 'Jane Doe',
        ]);
    }

    public function test_vat_id_and_founded_at_are_optional(): void
    {
        $profile = (new DeProfile)->normalize([
            'legal_form' => LegalForm::Ev->value,
            'representative' => 'Vorstand',
            'register_court' => 'Amtsgericht Köln',
            'register_number' => 'VR 1234',
        ]);

        $this->assertArrayNotHasKey('vat_id', $profile);
        $this->assertArrayNotHasKey('founded_at', $profile);
    }

    public function test_unknown_de_keys_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        (new DeProfile)->normalize([
            'legal_form' => LegalForm::Gbr->value,
            'representative' => 'Jane Doe',
            'steuernummer' => '123',
        ]);
    }

    public function test_non_de_uses_generic_profile(): void
    {
        $this->assertSame('generic', CountryProfileRegistry::for('US')->key());
        $this->assertSame('generic', CountryProfileRegistry::for('AT')->key());
        $this->assertSame('DE', CountryProfileRegistry::for('DE')->key());
        $this->assertTrue(CountryProfileRegistry::isDe('de'));
        $this->assertFalse(CountryProfileRegistry::isDe('US'));
    }

    public function test_gmbh_requires_register(): void
    {
        $this->assertTrue(LegalForm::Gmbh->requiresRegister());
        $this->assertTrue(LegalForm::Ev->requiresRegister());
        $this->assertFalse(LegalForm::Nev->requiresRegister());
        $this->assertFalse(LegalForm::Stiftung->requiresRegister());
        $this->assertTrue(LegalForm::Stiftung->showsRegisterFields());
    }
}
