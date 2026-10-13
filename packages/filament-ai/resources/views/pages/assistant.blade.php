<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div
                class="flex min-h-[20rem] max-h-[32rem] flex-col gap-4 overflow-y-auto"
                wire:key="filament-ai-messages"
            >
                @forelse ($messages as $message)
                    <div @class([
                        'flex w-full',
                        'justify-end' => ($message['role'] ?? '') === 'user',
                        'justify-start' => ($message['role'] ?? '') !== 'user',
                    ])>
                        <div @class([
                            'max-w-[85%] rounded-xl px-4 py-3 text-sm shadow-sm whitespace-pre-wrap',
                            'bg-primary-600 text-white' => ($message['role'] ?? '') === 'user',
                            'bg-white text-gray-950 ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10' => ($message['role'] ?? '') !== 'user',
                        ])>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide opacity-80">
                                {{ ($message['role'] ?? '') === 'user'
                                    ? __('filament-ai::ai.chat.you')
                                    : __('filament-ai::ai.chat.assistant') }}
                            </div>
                            <div class="leading-relaxed">{{ $message['content'] ?? '' }}</div>

                            @if (! empty($message['attachments']))
                                <ul class="mt-2 space-y-1 text-xs opacity-90">
                                    @foreach ($message['attachments'] as $attachment)
                                        <li>{{ $attachment['name'] ?? basename($attachment['path'] ?? '') }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('filament-ai::ai.chat.empty') }}
                    </p>
                @endforelse

                @if (filled($streamingReply))
                    <div class="flex w-full justify-start">
                        <div class="max-w-[85%] rounded-xl bg-white px-4 py-3 text-sm shadow-sm ring-1 ring-gray-950/10 whitespace-pre-wrap dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10">
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide opacity-80">
                                {{ __('filament-ai::ai.chat.assistant') }}
                            </div>
                            <div wire:stream="streamingReply" class="leading-relaxed">{{ $streamingReply }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <form wire:submit="sendMessage" class="space-y-4">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-2">
                @if (filled($activeTurnId))
                    <x-filament::button type="button" color="gray" wire:click="stopGeneration">
                        {{ __('filament-ai::ai.chat.stop') }}
                    </x-filament::button>
                @endif

                <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="sendMessage">
                    {{ __('filament-ai::ai.chat.send') }}
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
