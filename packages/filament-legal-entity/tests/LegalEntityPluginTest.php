<?php

namespace BeegoodIT\FilamentLegalEntity\Tests;

use BeegoodIT\FilamentLegalEntity\Filament\LegalEntityForm;
use BeegoodIT\FilamentLegalEntity\FilamentLegalEntityPlugin;
use BeegoodIT\FilamentLegalEntity\Support\Iso3166Countries;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class LegalEntityPluginTest extends TestCase
{
    public function test_resource_is_registered_by_default(): void
    {
        $plugin = FilamentLegalEntityPlugin::make();

        $this->assertTrue($plugin->registersResource());
        $this->assertSame('filament-legal-entity', $plugin->getId());
    }

    public function test_has_resource_false_skips_the_resource(): void
    {
        $plugin = FilamentLegalEntityPlugin::make()->hasResource(false);

        $this->assertFalse($plugin->registersResource());
    }

    public function test_form_schema_has_no_country_default(): void
    {
        $country = collect(LegalEntityForm::schema(includeOwner: false))
            ->first(fn (object $component): bool => $component instanceof Select && $component->getName() === 'country');

        $this->assertInstanceOf(Select::class, $country);
        $this->assertNull($country->getDefaultState());
        $this->assertTrue($country->isSearchable());
    }

    public function test_form_schema_includes_de_section_and_region(): void
    {
        $components = collect(LegalEntityForm::schema(includeOwner: false));

        $this->assertTrue($components->contains(fn (object $component): bool => $component instanceof Section));
        $this->assertTrue($components->contains(fn (object $component): bool => $component instanceof TextInput && $component->getName() === 'region'));
    }

    public function test_iso_list_includes_de_and_is_searchable_options(): void
    {
        $options = Iso3166Countries::options('en');

        $this->assertArrayHasKey('DE', $options);
        $this->assertArrayHasKey('US', $options);
        $this->assertTrue(Iso3166Countries::isValid('DE'));
        $this->assertFalse(Iso3166Countries::isValid('XX'));
    }

    public function test_center_schema_omits_owner_select(): void
    {
        $names = collect(LegalEntityForm::schema(includeOwner: false))
            ->map(fn (object $component): ?string => method_exists($component, 'getName') ? $component->getName() : null)
            ->all();

        $this->assertNotContains('owner', $names);
    }
}
