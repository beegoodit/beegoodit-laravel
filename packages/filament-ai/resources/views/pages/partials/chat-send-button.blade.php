<div class="flex flex-wrap items-center justify-end gap-2">
    <div wire:loading wire:target="sendMessage">
        <x-filament::button type="button" color="gray" wire:click="stopGeneration">
            {{ __('filament-ai::ai.chat.stop') }}
        </x-filament::button>
    </div>

    <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="sendMessage">
        {{ __('filament-ai::ai.chat.send') }}
    </x-filament::button>
</div>
