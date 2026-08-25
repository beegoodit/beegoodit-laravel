<?php

namespace BeegoodIT\FilamentTenancy\Tests\Unit;

use BeegoodIT\FilamentI18n\FilamentI18nServiceProvider;
use BeegoodIT\FilamentTenancy\Filament\Schemas\BrandingSchema;
use BeegoodIT\FilamentTenancy\Tests\TestCase;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

class BrandingSchemaDescriptionPhpUnitTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            FilamentI18nServiceProvider::class,
        ]);
    }

    public function test_description_migration_stub_adds_json_column(): void
    {
        $stub = file_get_contents(__DIR__.'/../../database/migrations/add_team_description.php.stub');

        $this->assertNotFalse($stub);
        $this->assertStringContainsString("json('description')", $stub);
        $this->assertStringContainsString('nullable()', $stub);
    }

    public function test_base_schema_orders_team_description_then_branding(): void
    {
        config(['filament-i18n.available_locales' => ['en', 'de']]);

        $schema = BrandingSchema::getBaseSchema(\Illuminate\Database\Eloquent\Model::class);

        $this->assertCount(3, $schema);
        $this->assertSame(__('filament-tenancy::messages.Team'), $schema[0]->getHeading());
        $this->assertSame(__('filament-tenancy::messages.Description'), $schema[1]->getHeading());
        $this->assertSame(__('filament-tenancy::messages.Branding'), $schema[2]->getHeading());
    }

    public function test_identity_section_is_name_and_slug_only(): void
    {
        config(['filament-i18n.available_locales' => ['en']]);

        $section = BrandingSchema::getIdentitySection(\Illuminate\Database\Eloquent\Model::class);
        $children = $section->getDefaultChildComponents();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(TextInput::class, $children[0]);
        $this->assertSame('name', $children[0]->getName());
        $this->assertInstanceOf(TextInput::class, $children[1]);
        $this->assertSame('slug', $children[1]->getName());
    }

    public function test_single_locale_uses_rich_editor_without_tabs(): void
    {
        config(['filament-i18n.available_locales' => ['en']]);

        $components = BrandingSchema::descriptionEditorComponents();

        $this->assertCount(1, $components);
        $this->assertInstanceOf(RichEditor::class, $components[0]);
        $this->assertSame('description.en', $components[0]->getName());
    }

    public function test_multiple_locales_use_tabs(): void
    {
        config(['filament-i18n.available_locales' => ['en', 'de']]);

        $components = BrandingSchema::descriptionEditorComponents();

        $this->assertCount(1, $components);
        $this->assertInstanceOf(Tabs::class, $components[0]);
    }

    public function test_description_section_contains_editors(): void
    {
        config(['filament-i18n.available_locales' => ['en']]);

        $section = BrandingSchema::getDescriptionSection();

        $this->assertInstanceOf(Section::class, $section);
        $this->assertSame(__('filament-tenancy::messages.Description'), $section->getHeading());
        $children = $section->getDefaultChildComponents();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RichEditor::class, $children[0]);
    }
}
