<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Tests;

use BeegoodIT\Pdf\LaravelPdfRendererServiceProvider;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            LaravelPdfRendererServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('laravel-pdf-renderer.fake', true);
    }
}
