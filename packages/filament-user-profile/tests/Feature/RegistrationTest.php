<?php

namespace BeeGoodIT\FilamentUserProfile\Tests\Feature;

use BeeGoodIT\FilamentUserProfile\Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_is_conditionally_called_in_panel_provider()
    {
        $panelProviderFile = __DIR__ . '/../../src/Filament/UserProfilePanelProvider.php';

        $this->assertFileExists($panelProviderFile);

        $content = file_get_contents($panelProviderFile);

        // Verify that the config-based registration call exists
        $this->assertStringContainsString("->registration(config('filament-user-profile.registration', false))", $content);
    }

    public function test_config_file_exists_with_default_false()
    {
        $configFile = __DIR__ . '/../../config/filament-user-profile.php';

        $this->assertFileExists($configFile);

        $config = require $configFile;

        $this->assertArrayHasKey('registration', $config);
        $this->assertFalse($config['registration']);
    }
}
