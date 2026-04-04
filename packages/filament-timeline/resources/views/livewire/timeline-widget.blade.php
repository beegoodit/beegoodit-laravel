<x-filament-widgets::widget class="min-w-0">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .tl-fade-left {
            background: linear-gradient(to right, white 0%, color-mix(in srgb, white 80%, transparent) 50%, transparent 100%);
        }
        .tl-fade-right {
            background: linear-gradient(to left, white 0%, color-mix(in srgb, white 80%, transparent) 50%, transparent 100%);
        }
        .dark .tl-fade-left {
            background: linear-gradient(to right, var(--color-gray-900) 0%, color-mix(in srgb, var(--color-gray-900) 80%, transparent) 50%, transparent 100%);
        }
        .dark .tl-fade-right {
            background: linear-gradient(to left, var(--color-gray-900) 0%, color-mix(in srgb, var(--color-gray-900) 80%, transparent) 50%, transparent 100%);
        }
    </style>
    <x-filament::section class="min-w-0 overflow-hidden">
        <x-slot name="heading">
            {{ __('filament-timeline::messages.History') }}
        </x-slot>
        <x-slot name="headerEnd">
            <button 
                wire:click="refresh"
                wire:loading.attr="disabled"
                wire:target="refresh"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 disabled:opacity-50"
                title="{{ __('filament-timeline::messages.Refresh history') }}"
            >
                <div wire:loading.class="animate-spin" wire:target="refresh">
                    <x-heroicon-m-arrow-path class="w-5 h-5" />
                </div>
            </button>
        </x-slot>

        <div class="relative group/container min-w-0 overflow-hidden">
        @if ($direction === 'horizontal')
            <div
                x-data="{
                    scrolledToStart: true,
                    scrolledToEnd: false,
                    activeItem: 0,
                    itemWidth: 312,

                    updateScroll() {
                        const el = this.$refs.viewport;
                        this.scrolledToStart = el.scrollLeft <= 5;
                        this.scrolledToEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 5;
                        this.activeItem = Math.round(el.scrollLeft / this.itemWidth);
                    },

                    scroll(direction) {
                        this.$refs.viewport.scrollBy({
                            left: direction === 'left' ? -this.itemWidth : this.itemWidth,
                            behavior: 'smooth',
                        });
                    },

                    scrollToItem(index) {
                        this.$refs.viewport.scrollTo({
                            left: index * this.itemWidth,
                            behavior: 'smooth',
                        });
                    },

                    measure() {
                        const firstItem = this.$refs.row?.querySelector('[data-timeline-item]');
                        if (! firstItem) return;

                        const styles = window.getComputedStyle(this.$refs.row);
                        const gap = parseFloat(styles.columnGap || styles.gap || 0);
                        this.itemWidth = firstItem.offsetWidth + gap;
                    },
                }"
                x-init="
                    $nextTick(() => {
                        measure();
                        updateScroll();
                        window.addEventListener('resize', () => {
                            measure();
                            updateScroll();
                        });
                    });
                "
                class="relative w-full min-w-0 max-w-full"
            >
                <div
                    x-show="!scrolledToStart"
                    x-transition.opacity
                    class="absolute left-0 top-0 bottom-0 z-20 hidden md:flex items-center pr-12 tl-fade-left pointer-events-none"
                >
                    <button
                        type="button"
                        @click="scroll('left')"
                        class="ml-2 pointer-events-auto rounded-full border border-gray-100 bg-white p-2 shadow-lg transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <x-heroicon-o-chevron-left class="h-5 w-5 text-gray-600 dark:text-gray-300" />
                    </button>
                </div>

                <div
                    x-show="!scrolledToEnd"
                    x-transition.opacity
                    class="absolute right-0 top-0 bottom-0 z-20 hidden md:flex items-center pl-12 tl-fade-right pointer-events-none"
                >
                    <button
                        type="button"
                        @click="scroll('right')"
                        class="mr-2 pointer-events-auto rounded-full border border-gray-100 bg-white p-2 shadow-lg transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-600 dark:text-gray-300" />
                    </button>
                </div>

                <div
                    wire:loading.class="opacity-50 transition-opacity duration-500"
                    wire:target="refresh"
                    x-ref="viewport"
                    @scroll.debounce.50ms="updateScroll"
                    class="w-full min-w-0 max-w-full overflow-x-auto overflow-y-hidden scrollbar-hide scroll-smooth"
                >
                    <div
                        x-ref="row"
                        class="flex w-max min-w-full gap-6 pt-4 pb-4 snap-x snap-mandatory"
                    >
                        @forelse ($entries as $entry)
                            <div
                                data-timeline-item
                                class="group w-72 shrink-0 snap-start"
                            >
                                @if($entry->url)
                                    <a href="{{ $entry->url }}" class="block hover:no-underline">
                                @endif

                                <div class="mb-3 flex items-center gap-3">
                                    <div
                                        class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 bg-white shadow-sm ring-4 ring-white transition-transform group-hover:scale-110 dark:bg-gray-800 dark:ring-gray-800"
                                        style="border-color: {{ $entry->color ?? 'var(--color-gray-200)' }}"
                                    >
                                        @if ($entry->icon)
                                            <x-filament::icon
                                                :icon="$entry->icon"
                                                class="h-5 w-5"
                                                :style="$entry->color ? 'color: ' . $entry->color : ''"
                                            />
                                        @else
                                            <div class="h-3 w-3 rounded-full bg-gray-400"></div>
                                        @endif
                                    </div>

                                    <div class="h-0.5 grow bg-gray-200 dark:bg-gray-700"></div>

                                    @if($entry->occurredAt)
                                        <time
                                            class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wider text-gray-500"
                                            title="{{ $entry->occurredAt->toDateTimeString() }}"
                                        >
                                            {{ $entry->occurredAt->diffForHumans() }}
                                        </time>
                                    @endif
                                </div>

                                <div class="pl-2">
                                    <h4 class="text-sm font-bold leading-tight text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                        {{ $entry->title }}
                                    </h4>

                                    @if ($entry->description)
                                        <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                                            {{ $entry->description }}
                                        </p>
                                    @endif
                                </div>

                                @if($entry->url)
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="w-full py-4 text-center text-sm text-gray-500">
                                {{ __('filament-timeline::messages.No history items found.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($entries->count() > 1)
                    @php
                        $globalIndex = 0;
                        $groups = $this->getGroupedEntries();
                    @endphp

                    <div class="mt-6 flex min-w-0 max-w-full flex-wrap justify-center gap-x-8 gap-y-4 px-4 md:px-12">
                        @foreach ($groups as $year => $groupEntries)
                            <div class="flex flex-col items-center gap-1.5">
                                <span
                                    class="text-[9px] font-bold uppercase tracking-tighter transition-colors duration-300"
                                    :class="[
                                        @foreach($groupEntries as $idx => $e)
                                            activeItem === {{ $globalIndex + $idx }} {{ !$loop->last ? '||' : '' }}
                                        @endforeach
                                    ] ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                                >
                                    {{ $year }}
                                </span>

                                <div class="flex gap-1.5">
                                    @foreach ($groupEntries as $entry)
                                        <button
                                            type="button"
                                            @click="scrollToItem({{ $globalIndex }})"
                                            :class="activeItem === {{ $globalIndex }} ? 'w-6 opacity-100' : 'w-2 opacity-40 hover:opacity-60'"
                                            class="h-2 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                            style="background-color: {{ $entry->color ?? 'var(--color-gray-400)' }}"
                                            title="{{ $entry->title }} ({{ $year }})"
                                        ></button>
                                        @php $globalIndex++; @endphp
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @else
                {{-- Vertical Line --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                <div 
                    wire:loading.class="opacity-50 transition-opacity duration-500"
                    wire:target="refresh"
                    class="space-y-8 relative"
                >
                    @forelse ($entries as $entry)
                        <div class="flex gap-4 group">
                            <div 
                                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white dark:bg-gray-800 ring-4 ring-white dark:ring-gray-900 overflow-hidden shadow-sm border-2"
                                style="border-color: {{ $entry->color ?? 'var(--color-gray-200)' }}"
                            >
                                @if ($entry->icon)
                                    <x-filament::icon 
                                        :icon="$entry->icon" 
                                        class="h-4 w-4"
                                        :style="$entry->color ? 'color: ' . $entry->color : ''"
                                    />
                                @else
                                    <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 pt-1">
                                <div class="flex items-baseline justify-between gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                        @if ($entry->url)
                                            <a href="{{ $entry->url }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                                {{ $entry->title }}
                                            </a>
                                        @else
                                            {{ $entry->title }}
                                        @endif
                                    </h4>
                                    @if($entry->occurredAt)
                                        <time class="text-xs text-gray-500 whitespace-nowrap" title="{{ $entry->occurredAt->toDateTimeString() }}">
                                            {{ $entry->occurredAt->diffForHumans() }}
                                        </time>
                                    @endif
                                </div>
                                
                                @if ($entry->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                        {{ $entry->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-gray-500">
                            {{ __('filament-timeline::messages.No history items found.') }}
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
