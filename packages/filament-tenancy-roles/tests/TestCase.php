<?php

namespace BeegoodIT\FilamentTenancyRoles\Tests;

use BeegoodIT\FilamentTenancyRoles\FilamentTenancyRolesServiceProvider;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
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

        // Bind translator so __() works in enums (e.g. TeamRole::label())
        $app->singleton('translator', fn () => new Translator(new ArrayLoader, 'en'));
    }
}
