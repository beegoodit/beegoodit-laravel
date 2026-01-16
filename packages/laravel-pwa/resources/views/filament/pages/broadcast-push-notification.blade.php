<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Broadcast Notification') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Send a push notification to specific or all subscribed users.') }}
        </x-slot>

        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    {{ __('Send Notification') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
