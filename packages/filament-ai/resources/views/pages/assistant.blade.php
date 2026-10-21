<x-filament-panels::page :full-height="true">
    <div class="filament-ai-assistant-layout filament-ai-assistant-layout--with-sidebar">
        <div class="filament-ai-assistant-chat">
            <x-filament::section>
                <div
                    class="filament-ai-assistant-messages"
                    wire:key="filament-ai-messages"
                    x-data="{
                        scrollToBottom() {
                            this.$nextTick(() => {
                                this.$el.scrollTop = this.$el.scrollHeight
                            })
                        }
                    }"
                    x-init="
                        scrollToBottom();
                        new MutationObserver(() => scrollToBottom()).observe($el, {
                            childList: true,
                            subtree: true,
                            characterData: true,
                        });
                    "
                    x-effect="
                        $wire.messages;
                        $wire.streamingReply;
                        scrollToBottom();
                    "
                >
                    @forelse ($messages as $message)
                        @php
                            $isUser = ($message['role'] ?? '') === 'user';
                        @endphp
                        <div @class([
                            'flex w-full',
                            'justify-end' => $isUser,
                            'justify-start' => ! $isUser,
                        ])>
                            <div @class([
                                'max-w-[85%] rounded-xl px-4 py-3 text-sm shadow-sm',
                                'whitespace-pre-wrap bg-primary-600 text-white' => $isUser,
                                'bg-white text-gray-950 ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10' => ! $isUser,
                            ])>
                                <div class="mb-1 text-xs font-semibold uppercase tracking-wide opacity-80">
                                    {{ $isUser
                                        ? __('filament-ai::ai.chat.you')
                                        : __('filament-ai::ai.chat.assistant') }}
                                </div>
                                @if ($isUser)
                                    <div class="leading-relaxed">{{ $message['content'] ?? '' }}</div>
                                @else
                                    <div class="filament-ai-assistant-markdown leading-relaxed">
                                        {!! \BeegoodIT\FilamentAi\Support\ChatMarkdown::toHtml((string) ($message['content'] ?? '')) !!}
                                    </div>
                                @endif

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
                            <div class="max-w-[85%] rounded-xl bg-white px-4 py-3 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10">
                                <div class="mb-1 text-xs font-semibold uppercase tracking-wide opacity-80">
                                    {{ __('filament-ai::ai.chat.assistant') }}
                                </div>
                                <div wire:stream="streamingReply" class="filament-ai-assistant-markdown leading-relaxed">
                                    {!! \BeegoodIT\FilamentAi\Support\ChatMarkdown::toHtml($streamingReply) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <form
                    wire:submit="sendMessage"
                    class="filament-ai-assistant-composer fi-fixed-positioning-context space-y-4 [&_.fi-fo-field-wrp]:mb-0"
                    x-data="{
                        syncAttachmentHeight() {
                            const prompt = this.$el.querySelector('.filament-ai-composer-prompt textarea')
                            const pond = this.$el.querySelector('.filament-ai-composer-attachments .filepond--root')

                            if (! prompt || ! pond) {
                                return
                            }

                            const height = `${prompt.offsetHeight}px`
                            pond.style.setProperty('height', height, 'important')
                            pond.style.setProperty('min-height', height, 'important')

                            pond.querySelectorAll('.filepond--drop-label, .filepond--drop-label label').forEach((el) => {
                                el.style.setProperty('transform', 'none', 'important')
                            })
                        },
                        placeSelectDropdowns() {
                            this.$el.querySelectorAll('.filament-ai-composer-controls .fi-select-input-ctn').forEach((ctn) => {
                                const btn = ctn.querySelector('.fi-select-input-btn')
                                const panel = ctn.querySelector('.fi-dropdown-panel')

                                if (! btn || ! panel) {
                                    return
                                }

                                const visible = panel.style.display !== 'none'
                                    && window.getComputedStyle(panel).display !== 'none'

                                if (! visible) {
                                    return
                                }

                                const isModel = Boolean(ctn.closest('.filament-ai-composer-model'))
                                const rect = btn.getBoundingClientRect()
                                const width = isModel
                                    ? Math.max(rect.width, 288)
                                    : Math.max(rect.width, 8 * 16)

                                panel.style.setProperty('position', 'fixed', 'important')
                                panel.style.setProperty('z-index', '80', 'important')
                                panel.style.setProperty('width', `${width}px`, 'important')
                                panel.style.setProperty('min-width', `${width}px`, 'important')
                                panel.style.setProperty('max-width', 'min(24rem, calc(100vw - 1rem))', 'important')

                                const gap = 4
                                const top = Math.max(8, rect.top - panel.offsetHeight - gap)
                                panel.style.setProperty('left', `${rect.left}px`, 'important')
                                panel.style.setProperty('top', `${top}px`, 'important')
                                panel.style.setProperty('bottom', 'auto', 'important')
                            })
                        },
                    }"
                    x-init="
                        const run = () => $nextTick(() => syncAttachmentHeight())
                        run()
                        const prompt = $el.querySelector('.filament-ai-composer-prompt textarea')
                        if (prompt) {
                            new ResizeObserver(run).observe(prompt)
                        }
                        new MutationObserver(run).observe($el, { childList: true, subtree: true })
                        $wire && $wire.$watch && $wire.$watch('attachments', run)

                        const place = () => requestAnimationFrame(() => placeSelectDropdowns())
                        new MutationObserver(place).observe($el, {
                            childList: true,
                            subtree: true,
                            attributes: true,
                            attributeFilter: ['style', 'class', 'aria-expanded'],
                        })
                        window.addEventListener('scroll', place, true)
                        window.addEventListener('resize', place)
                    "
                >
                    {{ $this->form }}
                </form>
            </x-filament::section>
        </div>

        <aside class="filament-ai-assistant-sidebar">
            <x-filament::section>
                @include('filament-ai::pages.partials.chat-sidebar', [
                    'conversationCreatedAt' => $conversationCreatedAt,
                    'conversationUpdatedAt' => $conversationUpdatedAt,
                ])
            </x-filament::section>
        </aside>
    </div>
</x-filament-panels::page>
