@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Enums\Size;

    $periodStart = $this->getDateTimePickerStart();
    $periodEnd = $this->getDateTimePickerEnd();
    $selectedDay = filled($periodStart) ? (int) substr((string) $periodStart, 8, 2) : null;
    $isSingleDay = filled($periodStart) && $periodStart === $periodEnd;
@endphp

<div
    class="relative"
    x-data="{
        year: {{ $this->dateTimePickerYear() }},
        dayMonth: {{ $this->dateTimePickerMonth() }},
        selectedYear: {{ $this->dateTimePickerYear() }},
        selectedMonth: {{ $this->dateTimePickerMonth() }},
        selectedDay: {{ $selectedDay === null ? 'null' : $selectedDay }},
        singleDay: {{ $isSingleDay ? 'true' : 'false' }},
        isMonthSelected(month) {
            return this.year === this.selectedYear && month === this.selectedMonth
        },
        isDaySelected(day) {
            return this.singleDay
                && this.year === this.selectedYear
                && this.dayMonth === this.selectedMonth
                && day === this.selectedDay
        },
        calendarCells() {
            const first = new Date(this.year, this.dayMonth - 1, 1)
            const daysInMonth = new Date(this.year, this.dayMonth, 0).getDate()
            const mondayFirstOffset = (first.getDay() + 6) % 7
            const cells = []

            for (let index = 0; index < mondayFirstOffset; index++) {
                cells.push(null)
            }

            for (let day = 1; day <= daysInMonth; day++) {
                cells.push(day)
            }

            return cells
        },
        chooseYear() {
            $wire.dateTimePickerGoToYear(this.year)
        },
        chooseQuarter(quarter) {
            $wire.dateTimePickerGoToQuarter(this.year, quarter)
        },
        chooseMonth(month) {
            this.dayMonth = month
            this.selectedYear = this.year
            this.selectedMonth = month
            this.singleDay = false
            $wire.dateTimePickerGoToMonth(this.year, month)
        },
        chooseDay(day) {
            $wire.dateTimePickerGoToDay(this.year, this.dayMonth, day)
        },
    }"
    x-on:click.outside="if ($wire.dateTimePickerOpen) $wire.closeDateTimePicker()"
    x-on:keydown.escape.window="if ($wire.dateTimePickerOpen) $wire.closeDateTimePicker()"
>
    <x-filament::button.group>
        <x-filament::button
            icon="heroicon-m-chevron-left"
            label-sr-only
            :tooltip="__('datetime-picker::picker.previous')"
            wire:click="dateTimePickerPrevious"
        >
            {{ __('datetime-picker::picker.previous') }}
        </x-filament::button>

        <x-filament::button
            icon="heroicon-m-chevron-down"
            :icon-position="IconPosition::After"
            wire:click="toggleDateTimePicker"
            :aria-expanded="$this->dateTimePickerOpen ? 'true' : 'false'"
            type="button"
        >
            {{ $this->dateTimePickerLabel() }}
        </x-filament::button>

        <x-filament::button
            icon="heroicon-m-chevron-right"
            label-sr-only
            :tooltip="__('datetime-picker::picker.next')"
            wire:click="dateTimePickerNext"
        >
            {{ __('datetime-picker::picker.next') }}
        </x-filament::button>
    </x-filament::button.group>

    @if ($this->dateTimePickerOpen)
        <div
            class="fi-dropdown-panel absolute end-0 z-20 mt-2 min-w-64 p-2"
            role="menu"
        >
            <div class="flex items-center justify-between gap-2">
                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    :size="Size::Small"
                    :tooltip="__('datetime-picker::picker.previous_year')"
                    x-on:click="year--"
                    type="button"
                />

                <button
                    type="button"
                    class="min-w-16 rounded-md px-2 py-1 text-center text-sm font-medium tabular-nums text-gray-950 transition hover:bg-gray-400/10 dark:text-white dark:hover:bg-white/5"
                    x-on:click="chooseYear()"
                    x-text="year"
                ></button>

                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    :size="Size::Small"
                    :tooltip="__('datetime-picker::picker.next_year')"
                    x-on:click="year++"
                    type="button"
                />
            </div>

            <div class="mt-1 grid grid-cols-4 gap-1">
                @foreach ([1, 2, 3, 4] as $quarter)
                    <button
                        type="button"
                        class="rounded-md px-2 py-1.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-400/10 dark:text-gray-200 dark:hover:bg-white/5"
                        x-on:click="chooseQuarter({{ $quarter }})"
                    >
                        {{ __('datetime-picker::picker.quarter', ['quarter' => $quarter]) }}
                    </button>
                @endforeach
            </div>

            <div class="mt-1 grid grid-cols-4 gap-1">
                @foreach (range(1, 12) as $month)
                    <button
                        type="button"
                        class="rounded-full px-2 py-1.5 text-center text-sm font-medium uppercase tracking-wide transition"
                        x-bind:class="isMonthSelected({{ $month }})
                            ? 'bg-primary-600 text-white dark:bg-primary-500'
                            : 'text-gray-700 hover:bg-gray-400/10 dark:text-gray-200 dark:hover:bg-white/5'"
                        x-on:click="chooseMonth({{ $month }})"
                    >
                        {{ __('datetime-picker::picker.months_short.'.$month) }}
                    </button>
                @endforeach
            </div>

            <div class="mt-2 pt-2">
                <div class="grid grid-cols-7 gap-1">
                    @foreach (__('datetime-picker::picker.weekdays_short') as $weekday)
                        <div class="py-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $weekday }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(cell, index) in calendarCells()" :key="`${year}-${dayMonth}-${index}`">
                        <div>
                            <button
                                type="button"
                                class="flex h-8 w-full items-center justify-center rounded-full text-sm font-medium transition"
                                x-show="cell !== null"
                                x-bind:class="isDaySelected(cell)
                                    ? 'bg-primary-600 text-white dark:bg-primary-500'
                                    : 'text-gray-700 hover:bg-gray-400/10 dark:text-gray-200 dark:hover:bg-white/5'"
                                x-on:click="chooseDay(cell)"
                                x-text="cell"
                            ></button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-2 grid grid-cols-2 gap-2 py-2">
                <x-filament::button
                    color="gray"
                    wire:click="dateTimePickerGoToToday"
                    class="w-full justify-center"
                >
                    {{ __('datetime-picker::picker.today') }}
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="dateTimePickerReset"
                    class="w-full justify-center"
                >
                    {{ __('datetime-picker::picker.reset') }}
                </x-filament::button>
            </div>
        </div>
    @endif
</div>
