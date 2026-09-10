<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Tests\Feature;

use BeegoodIT\FilamentGroupSettings\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MakeGroupSettingsPageCommandTest extends TestCase
{
    #[Test]
    public function it_stubs_a_group_settings_page(): void
    {
        $path = app_path('Filament/Pages/Settings/BillingSettings.php');

        $this->artisan('make:filament-group-settings-page', [
            'name' => 'BillingSettings',
            '--group-key' => 'billing',
            '--navigation-group' => 'navigation.groups.billing',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertStringContainsString("return 'billing';", $contents);
        $this->assertStringContainsString("return __('navigation.groups.billing');", $contents);
        $this->assertStringContainsString('extends GroupSettingsPage', $contents);
        $this->assertStringContainsString('topicTabs(', $contents);
        $this->assertStringContainsString('Tab::make(', $contents);
        $this->assertStringContainsString('Section::make()', $contents);
    }

    #[Test]
    public function it_requires_a_navigation_group(): void
    {
        $this->artisan('make:filament-group-settings-page', [
            'name' => 'BillingSettings',
            '--group-key' => 'billing',
            '--force' => true,
        ])->assertFailed();
    }
}
