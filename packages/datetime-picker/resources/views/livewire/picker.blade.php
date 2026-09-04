@php
    $periodStart = $start ?? null;
    $periodEnd = $end ?? null;
    $selectedDay = filled($periodStart) ? (int) substr((string) $periodStart, 8, 2) : null;
    $isSingleDay = filled($periodStart) && $periodStart === $periodEnd;
@endphp

<div
    class="relative inline-block"
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
    <div class="inline-flex overflow-hidden rounded-lg shadow-sm ring-1 ring-gray-950/10 dark:ring-white/20">
        <button
            type="button"
            class="bg-white px-2.5 py-1.5 text-sm text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
            wire:click="dateTimePickerPrevious"
            title="{{ __('datetime-picker::picker.previous') }}"
        >
            ‹
        </button>
        <button
            type="button"
            class="bg-white px-3 py-1.5 text-sm font-medium text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
            wire:click="toggleDateTimePicker"
            aria-expanded="{{ $dateTimePickerOpen ? 'true' : 'false' }}"
        >
            {{ $this->dateTimePickerLabel() }} ▾
        </button>
        <button
            type="button"
            class="bg-white px-2.5 py-1.5 text-sm text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
            wire:click="dateTimePickerNext"
            title="{{ __('datetime-picker::picker.next') }}"
        >
            ›
        </button>
    </div>

    @if ($dateTimePickerOpen)
        <div class="absolute end-0 z-20 mt-2 min-w-64 rounded-lg bg-white p-2 shadow-lg ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between gap-2">
                <button type="button" class="rounded px-2 py-1 text-sm hover:bg-gray-100 dark:hover:bg-white/10" x-on:click="year--">‹</button>
                <button
                    type="button"
                    class="min-w-16 rounded px-2 py-1 text-center text-sm font-medium tabular-nums hover:bg-gray-100 dark:hover:bg-white/10"
                    x-on:click="chooseYear()"
                    x-text="year"
                ></button>
                <button type="button" class="rounded px-2 py-1 text-sm hover:bg-gray-100 dark:hover:bg-white/10" x-on:click="year++">›</button>
            </div>

            <div class="mt-1 grid grid-cols-4 gap-1">
                @foreach ([1, 2, 3, 4] as $quarter)
                    <button
                        type="button"
                        class="rounded-md px-2 py-1.5 text-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-white/10"
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
                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                            : 'hover:bg-gray-100 dark:hover:bg-white/10'"
                        x-on:click="chooseMonth({{ $month }})"
                    >
                        {{ __('datetime-picker::picker.months_short.'.$month) }}
                    </button>
                @endforeach
            </div>

            <div class="mt-2 pt-2">
                <div class="grid grid-cols-7 gap-1">
                    @foreach (__('datetime-picker::picker.weekdays_short') as $weekday)
                        <div class="py-1 text-center text-xs font-medium text-gray-500">{{ $weekday }}</div>
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
                                    ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                    : 'hover:bg-gray-100 dark:hover:bg-white/10'"
                                x-on:click="chooseDay(cell)"
                                x-text="cell"
                            ></button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-2 grid grid-cols-2 gap-2 py-2">
                <button type="button" class="rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-gray-950/10 hover:bg-gray-50 dark:ring-white/20 dark:hover:bg-white/10" wire:click="dateTimePickerGoToToday">
                    {{ __('datetime-picker::picker.today') }}
                </button>
                <button type="button" class="rounded-md px-3 py-1.5 text-sm font-medium ring-1 ring-gray-950/10 hover:bg-gray-50 dark:ring-white/20 dark:hover:bg-white/10" wire:click="dateTimePickerReset">
                    {{ __('datetime-picker::picker.reset') }}
                </button>
            </div>
        </div>
    @endif
</div>
