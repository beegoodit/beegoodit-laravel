<?php

namespace BeeGoodIT\LaravelPwa\Tests;

use BeeGoodIT\LaravelPwa\LaravelPwaServiceProvider;
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
