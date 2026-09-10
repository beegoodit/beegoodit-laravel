<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Tests\Unit;

use BeegoodIT\FilamentGroupSettings\GroupSettingsPlugin;
use BeegoodIT\FilamentGroupSettings\Pages\SettingsLanding;
use BeegoodIT\FilamentGroupSettings\Tests\Fixtures\DummyInvoicingSettings;
use BeegoodIT\FilamentGroupSettings\Tests\Fixtures\DummyUngroupedSettings;
use BeegoodIT\FilamentGroupSettings\Tests\TestCase;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class GroupSettingsPluginTest extends TestCase
{
    #[Test]
    public function empty_plugin_does_not_register_pages(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('pages')->never();

        GroupSettingsPlugin::make()->register($panel);

        $this->assertFalse(GroupSettingsPlugin::make()->hasLanding());
    }

    #[Test]
    public function listed_pages_are_registered_on_the_panel(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('pages')
            ->once()
            ->with([DummyInvoicingSettings::class])
            ->andReturnSelf();

        GroupSettingsPlugin::make()
            ->pages([DummyInvoicingSettings::class])
            ->register($panel);
    }

    #[Test]
    public function landing_registers_the_index_page(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('pages')
            ->once()
            ->with([DummyInvoicingSettings::class, SettingsLanding::class])
            ->andReturnSelf();

        GroupSettingsPlugin::make()
            ->pages([DummyInvoicingSettings::class])
            ->landing()
            ->register($panel);
    }

    #[Test]
    public function plugin_id_is_stable(): void
    {
        $this->assertSame('filament-group-settings', GroupSettingsPlugin::make()->getId());
    }

    #[Test]
    public function pages_must_extend_group_settings_page(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GroupSettingsPlugin::make()->pages([stdClass::class]);
    }

    #[Test]
    public function pages_must_define_a_navigation_group(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GroupSettingsPlugin::make()->pages([DummyUngroupedSettings::class]);
    }

    #[Test]
    public function dummy_page_uses_locked_nav_chrome(): void
    {
        $this->assertSame('invoicing/settings', DummyInvoicingSettings::getSlug());
        $this->assertSame(Heroicon::OutlinedCog6Tooth, DummyInvoicingSettings::getNavigationIcon());
        $this->assertSame(99, DummyInvoicingSettings::getNavigationSort());
        $this->assertFalse(DummyInvoicingSettings::shouldRegisterNavigation());
        $this->assertFalse(SettingsLanding::shouldRegisterNavigation());
    }

    #[Test]
    public function page_title_is_settings_and_group_is_a_breadcrumb(): void
    {
        $page = new DummyInvoicingSettings;
        $settings = __('filament-group-settings::messages.Settings');

        $this->assertSame($settings, $page->getTitle());
        $this->assertSame(['Invoicing', $settings], array_values($page->getBreadcrumbs()));
    }
}
