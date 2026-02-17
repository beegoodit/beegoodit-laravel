<?php

namespace BeegoodIT\FilamentTimeline\Tests;

use BeegoodIT\FilamentTimeline\FilamentTimelineServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            FilamentTimelineServiceProvider::class,
        ];
    }
}
