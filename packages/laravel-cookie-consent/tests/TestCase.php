<?php

namespace BeeGoodIT\LaravelCookieConsent\Tests;

use BeeGoodIT\LaravelCookieConsent\LaravelCookieConsentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LaravelCookieConsentServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('view.paths', [
            __DIR__.'/../resources/views',
            resource_path('views'),
        ]);
    }
}

