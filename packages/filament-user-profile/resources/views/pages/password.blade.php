<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button type="submit">
                {{ __('filament-user-profile::messages.Save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

