<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Livewire;

use BeegoodIT\DateTimePicker\Concerns\InteractsWithDateTimePicker;
use Carbon\Carbon;
use Livewire\Component;

class DateTimePicker extends Component
{
    use InteractsWithDateTimePicker;

    public ?string $start = null;

    public ?string $end = null;

    public string $timezone = 'UTC';

    public function mount(?string $start = null, ?string $end = null, ?string $timezone = null): void
    {
        $this->timezone = $timezone ?? (string) config('app.timezone', 'UTC');

        if ($start !== null && $end !== null) {
            $this->start = $start;
            $this->end = $end;

            return;
        }

        [$defaultStart, $defaultEnd] = $this->dateTimePickerDefaultRange();
        $this->start = $defaultStart->toDateString();
        $this->end = $defaultEnd->toDateString();
    }

    protected function dateTimePickerTimezone(): string
    {
        return $this->timezone;
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

        $this->dispatch('datetime-picker-updated', start: $this->start, end: $this->end);
    }

    public function render()
    {
        return view('datetime-picker::livewire.picker');
    }
}
