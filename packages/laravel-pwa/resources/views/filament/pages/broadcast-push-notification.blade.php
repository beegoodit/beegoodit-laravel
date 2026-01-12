<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Broadcast Notification') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Send a push notification to all subscribed users.') }}
        </x-slot>

        <form wire:submit="submit" class="space-y-6">
            <div>
                <x-filament::input.wrapper label="{{ __('Title') }}">
                    <x-filament::input type="text" wire:model="title_input" required maxlength="255"
                        placeholder="{{ __('e.g. New Event Published!') }}" />
                </x-filament::input.wrapper>
                @error('title_input') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Body') }}</label>
                <textarea wire:model="body" required maxlength="500" rows="4"
                    class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500"
                    placeholder="{{ __('e.g. A new tournament is available in your city.') }}"></textarea>
                @error('body') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-filament::input.wrapper label="{{ __('Action URL (optional)') }}">
                    <x-filament::input type="url" wire:model="action_url" placeholder="https://..." />
                </x-filament::input.wrapper>
                @error('action_url') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    {{ __('Send Broadcast') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>