<?php

namespace BeegoodIT\LaravelPwa\Tests;

use BeegoodIT\LaravelPwa\LaravelPwaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelPwaServiceProvider::class,
        ];
    }
}
