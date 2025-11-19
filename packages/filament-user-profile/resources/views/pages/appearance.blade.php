<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Theme Selection (Client-side, localStorage) --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Theme') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Choose your preferred color scheme. This setting is saved in your browser.') }}
            </p>
            @include('filament-user-profile::components.theme-toggle')
        </div>

        {{-- Language Selection (Database-backed) --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Language') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Choose your preferred language.') }}
            </p>
            <div>
                @php
                    $localeField = $this->getLocaleField();
                @endphp
                @if($localeField)
                    {{ $localeField }}
                @endif
            </div>
        </div>

        {{-- Timezone Selection (Database-backed) --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Timezone') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Select your timezone.') }}
            </p>
            <div>
                @php
                    $timezoneField = $this->getTimezoneField();
                @endphp
                @if($timezoneField)
                    {{ $timezoneField }}
                @endif
            </div>
        </div>

        {{-- Time Format Selection (Database-backed) --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Time Format') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Choose how time is displayed.') }}
            </p>
            <div>
                @php
                    $timeFormatField = $this->getTimeFormatField();
                @endphp
                @if($timeFormatField)
                    {{ $timeFormatField }}
                @endif
            </div>
        </div>

        {{-- Save Button --}}
        <form wire:submit="submit">
            <div class="flex items-center gap-4">
                <x-filament::button type="submit">
                    {{ __('Save') }}
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
