<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Filament;

use Filament\Schemas\Components\View;

final class DateTimePicker
{
    public static function make(string $view = 'datetime-picker::filament.picker'): View
    {
        return View::make($view);
    }
}
