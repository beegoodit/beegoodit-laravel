<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('laravel-pwa::broadcast.heading') }}
        </x-slot>

        <x-slot name="description">
            {{ __('laravel-pwa::broadcast.description') }}
        </x-slot>

        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    {{ __('laravel-pwa::broadcast.buttons.send') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
