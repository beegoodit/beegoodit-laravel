<x-filament-panels::page>
    <x-filament::section>
        @if ($this->conversations->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('filament-ai::ai.pages.conversations.empty') }}
            </p>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($this->conversations as $conversation)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                {{ $conversation->title ?: __('filament-ai::ai.pages.conversations.untitled') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $conversation->chatMode()->value }}
                                · {{ $conversation->updated_at?->diffForHumans() }}
                            </p>
                        </div>
                        <x-filament::button
                            size="sm"
                            color="gray"
                            wire:click="openConversation('{{ $conversation->id }}')"
                        >
                            {{ __('filament-ai::ai.pages.conversations.open') }}
                        </x-filament::button>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-panels::page>
