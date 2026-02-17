<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline;

use BeegoodIT\FilamentTimeline\Services\TimelineAggregator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTimelineServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-timeline')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;
        $app->singleton(TimelineAggregator::class, fn (): \BeegoodIT\FilamentTimeline\Services\TimelineAggregator => new TimelineAggregator);
    }

    public function packageBooted(): void
    {
        \Livewire\Livewire::component('timeline-widget', \BeegoodIT\FilamentTimeline\Components\TimelineWidget::class);

        /** @var TimelineAggregator $aggregator */
        $aggregator = resolve(TimelineAggregator::class);

        /** @var array<int, class-string<\BeegoodIT\FilamentTimeline\Contracts\TimelineProvider>> $providers */
        $providers = config('filament-timeline.providers', []);

        $aggregator->registerProviders($providers);
    }
}
