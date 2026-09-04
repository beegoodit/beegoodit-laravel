<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker;

use BeegoodIT\DateTimePicker\Livewire\DateTimePicker;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class DateTimePickerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'datetime-picker');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'datetime-picker');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/datetime-picker'),
        ], 'datetime-picker-lang');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/datetime-picker'),
        ], 'datetime-picker-views');

        Livewire::component('datetime-picker', DateTimePicker::class);
    }
}
