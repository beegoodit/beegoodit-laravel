# beegoodit/datetime-picker

Livewire date/time range picker with year, quarter, month, and day selection. Optional Filament integration.

## Requirements

- PHP 8.2+
- Livewire 3 or 4
- Optional: Filament 4/5 for schema embedding and Filament-styled buttons

## Installation

```bash
composer require beegoodit/datetime-picker
```

## Livewire (any app)

```php
use BeegoodIT\DateTimePicker\Concerns\InteractsWithDateTimePicker;
use Carbon\Carbon;
use Livewire\Component;

class ReportFilters extends Component
{
    use InteractsWithDateTimePicker;

    public ?string $start = null;
    public ?string $end = null;

    protected function dateTimePickerTimezone(): string
    {
        return config('app.timezone');
    }

    protected function getDateTimePickerStart(): ?string
    {
        return $this->start;
    }

    protected function getDateTimePickerEnd(): ?string
    {
        return $this->end;
    }

    protected function setDateTimePickerRange(Carbon $start, Carbon $end): void
    {
        $this->start = $start->toDateString();
        $this->end = $end->toDateString();
    }
}
```

```blade
<x-datetime-picker::livewire />
```

Or use the built-in component:

```blade
<livewire:datetime-picker />
```

## Filament

```php
use BeegoodIT\DateTimePicker\Filament\DateTimePicker;

Section::make('Filters')
    ->afterHeader([
        DateTimePicker::make(),
    ])
```

The Livewire page/component must use `InteractsWithDateTimePicker`.

If you use Tailwind v4 for a Filament theme, include the package views in your `@source` so utility classes are not purged:

```css
@source '../../../../vendor/beegoodit/datetime-picker/resources/views/**/*.blade.php';
```

## Localization

Translations ship under `datetime-picker::picker.*` (EN + DE). Publish if needed:

```bash
php artisan vendor:publish --tag=datetime-picker-lang
```
