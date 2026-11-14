<div class="filament-ai-composer-send">
    <div wire:loading wire:target="sendMessage">
        <x-filament::button
            type="button"
            color="gray"
            icon="heroicon-m-stop"
            wire:click="stopGeneration"
            aria-label="{{ __('filament-ai::ai.chat.stop') }}"
        />
    </div>

    <div wire:loading.remove wire:target="sendMessage">
        <x-filament::button
            type="submit"
            icon="heroicon-m-arrow-up"
            aria-label="{{ __('filament-ai::ai.chat.send') }}"
        />
    </div>
</div>
