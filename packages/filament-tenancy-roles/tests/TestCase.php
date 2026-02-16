<?php

namespace BeegoodIT\FilamentTenancyRoles\Tests;

use BeegoodIT\FilamentTenancyRoles\FilamentTenancyRolesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FilamentTenancyRolesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
