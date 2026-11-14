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
                            'filament-ai-assistant-message-row flex w-full items-start',
                            'justify-end' => $isUser,
                            'justify-start' => ! $isUser,
                        ])>
                            <div @class([
                                'filament-ai-assistant-message',
                                'filament-ai-assistant-message--user' => $isUser,
                                'filament-ai-assistant-message--assistant rounded-xl bg-white px-4 py-3 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10' => ! $isUser,
                            ])>
                                <div class="filament-ai-assistant-message-label">
                                    {{ $isUser
                                        ? __('filament-ai::ai.chat.you')
                                        : __('filament-ai::ai.chat.assistant') }}
                                </div>
                                @if ($isUser)
                                    <div class="filament-ai-assistant-message-body leading-relaxed">{!! \BeegoodIT\FilamentAi\Support\ChatMarkdown::toUserHtml((string) ($message['content'] ?? '')) !!}</div>
                                @else
                                    <div class="filament-ai-assistant-markdown leading-relaxed">
                                        {!! \BeegoodIT\FilamentAi\Support\ChatMarkdown::toAssistantHtml((string) ($message['content'] ?? '')) !!}
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
                        <div class="filament-ai-assistant-empty text-sm text-gray-500 dark:text-gray-400">
                            {{ __('filament-ai::ai.chat.empty') }}
                        </div>
                    @endforelse

                    <div
                        wire:loading.flex
                        wire:target="sendMessage"
                        x-data="{
                            startedAt: Date.now(),
                            elapsed: '0:00',
                            hasReply: false,
                            timer: null,
                            tick() {
                                const seconds = Math.floor((Date.now() - this.startedAt) / 1000)
                                const mins = Math.floor(seconds / 60)
                                const secs = String(seconds % 60).padStart(2, '0')
                                this.elapsed = `${mins}:${secs}`
                            },
                            syncReply() {
                                this.hasReply = (this.$refs.replyStream?.textContent || '').trim().length > 0
                            },
                        }"
                        x-init="
                            syncReply()
                            new MutationObserver(() => syncReply()).observe($el, {
                                childList: true,
                                subtree: true,
                                characterData: true,
                            })
                            const syncVisibility = () => {
                                const visible = getComputedStyle($el).display !== 'none'
                                if (visible && ! timer) {
                                    startedAt = Date.now()
                                    tick()
                                    timer = setInterval(() => tick(), 1000)
                                }
                                if (! visible && timer) {
                                    clearInterval(timer)
                                    timer = null
                                    hasReply = false
                                }
                            }
                            syncVisibility()
                            new MutationObserver(syncVisibility).observe($el, {
                                attributes: true,
                                attributeFilter: ['style', 'class'],
                            })
                        "
                        class="filament-ai-assistant-message-row flex w-full items-start justify-start"
                    >
                        <div class="filament-ai-assistant-message filament-ai-assistant-message--assistant rounded-xl bg-white px-4 py-3 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10">
                            <div class="mb-1 flex items-center justify-between gap-3">
                                <div class="filament-ai-assistant-message-label mb-0">
                                    {{ __('filament-ai::ai.chat.assistant') }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>
                                        {{ __('filament-ai::ai.chat.working') }}
                                        <span aria-hidden="true"> · </span>
                                        <span x-text="elapsed"></span>
                                    </span>
                                    <x-filament::button type="button" size="xs" color="danger" wire:click="stopGeneration">
                                        {{ __('filament-ai::ai.chat.stop') }}
                                    </x-filament::button>
                                </div>
                            </div>

                            <div
                                x-show="! hasReply"
                                class="flex items-center gap-2 text-gray-600 dark:text-gray-300"
                            >
                                <x-filament::loading-indicator class="h-4 w-4" />
                                <span>{{ __('filament-ai::ai.chat.thinking') }}</span>
                            </div>

                            <div
                                x-ref="replyStream"
                                wire:stream="streamingReply"
                                class="filament-ai-assistant-markdown fi-prose leading-relaxed empty:hidden"
                            >{!! \BeegoodIT\FilamentAi\Support\ChatMarkdown::toHtml($streamingReply) !!}</div>
                        </div>
                    </div>
                </div>

                <form
                    wire:submit="sendMessage"
                    class="filament-ai-assistant-composer fi-fixed-positioning-context space-y-4 [&_.fi-fo-field-wrp]:mb-0"
                    x-data="{
                        placeAttach() {
                            const row = this.$el.querySelector('.filament-ai-composer-input-row')
                            const textarea = row?.querySelector('textarea')

                            if (! row || ! textarea || ! this.$refs.attachTemplate) {
                                return
                            }

                            let button = row.querySelector('.filament-ai-composer-attach')

                            if (! button) {
                                button = this.$refs.attachTemplate.content.firstElementChild.cloneNode(true)
                                button.addEventListener('click', (event) => {
                                    event.preventDefault()
                                    event.stopPropagation()
                                    this.$el.querySelector('.filament-ai-composer-attachments .filepond--browser')?.click()
                                })
                                row.appendChild(button)
                            }

                            const rowRect = row.getBoundingClientRect()
                            const textRect = textarea.getBoundingClientRect()
                            const textStyle = window.getComputedStyle(textarea)
                            const lineHeight = Number.parseFloat(textStyle.lineHeight) || textRect.height
                            const paddingTop = Number.parseFloat(textStyle.paddingTop) || 0
                            const size = button.offsetHeight || lineHeight
                            const top = (textRect.top - rowRect.top) + paddingTop + ((lineHeight - size) / 2)
                            const next = `${top}px`

                            if (button.style.top !== next) {
                                button.style.top = next
                            }
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
                        const place = () => requestAnimationFrame(() => {
                            placeAttach()
                            placeSelectDropdowns()
                        })
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
                    <template x-ref="attachTemplate">
                        <button
                            type="button"
                            class="filament-ai-composer-attach"
                            aria-label="{{ __('filament-ai::ai.chat.attachments_browse') }}"
                            title="{{ __('filament-ai::ai.chat.attachments_browse') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                    </template>

                    {{ $this->form }}
                </form>
            </x-filament::section>
        </div>

        <aside class="filament-ai-assistant-sidebar">
            <x-filament::section :heading="$this->chatHeading()" heading-tag="h1">
                @include('filament-ai::pages.partials.chat-sidebar', [
                    'conversationCreatedAt' => $conversationCreatedAt,
                    'conversationUpdatedAt' => $conversationUpdatedAt,
                ])
            </x-filament::section>
        </aside>
    </div>
</x-filament-panels::page>
