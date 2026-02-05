<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <x-filament-actions::modals />

        <div class="mt-6 flex justify-end">
            {{ $this->submitAction }}
        </div>
    </form>
</x-filament-panels::page>
