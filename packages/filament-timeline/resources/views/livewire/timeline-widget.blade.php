<x-filament-widgets::widget>
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
    <x-filament::section>
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

        <div class="relative group/container">
            @if ($direction === 'horizontal')
                <div 
                    x-data="{
                        scrolledToStart: true,
                        scrolledToEnd: false,
                        activeItem: 0,
                        itemWidth: 312,
                        isDragging: false,
                        startX: 0,
                        scrollLeft: 0,
                        
                        updateScroll() {
                            const el = this.$refs.scrollContainer;
                            this.scrolledToStart = el.scrollLeft <= 5;
                            this.scrolledToEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 5;
                            
                            this.activeItem = Math.round(el.scrollLeft / this.itemWidth);
                        },

                        onMouseDown(e) {
                            this.isDragging = true;
                            this.startX = e.pageX - this.$refs.scrollContainer.offsetLeft;
                            this.scrollLeft = this.$refs.scrollContainer.scrollLeft;
                        },

                        onMouseUp() {
                            this.isDragging = false;
                        },

                        onMouseMove(e) {
                            if (!this.isDragging) return;
                            e.preventDefault();
                            const x = e.pageX - this.$refs.scrollContainer.offsetLeft;
                            const walk = (x - this.startX) * 2;
                            this.$refs.scrollContainer.scrollLeft = this.scrollLeft - walk;
                        },

                        scroll(direction) {
                            this.$refs.scrollContainer.scrollBy({
                                left: direction === 'left' ? -this.itemWidth : this.itemWidth,
                                behavior: 'smooth'
                            });
                        },

                        scrollToItem(index) {
                            this.$refs.scrollContainer.scrollTo({
                                left: index * this.itemWidth,
                                behavior: 'smooth'
                            });
                        }
                    }"
                    x-init="
                        setTimeout(() => {
                            const item = $refs.scrollContainer.querySelector('.snap-start');
                            if (item) {
                                itemWidth = item.offsetWidth + parseInt(window.getComputedStyle($refs.scrollContainer).gap || 0);
                            }
                            $refs.scrollContainer.scrollLeft = $refs.scrollContainer.scrollWidth;
                            updateScroll();
                        }, 100);
                    "
                    class="relative"
                >
                    {{-- Navigation Buttons --}}
                    <div 
                        x-show="!scrolledToStart" 
                        x-transition.opacity 
                        class="absolute left-0 top-0 bottom-0 z-20 flex items-center pr-12 tl-fade-left pointer-events-none group-hover/container:opacity-100 transition-opacity"
                    >
                        <button 
                            @click="scroll('left')" 
                            class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-100 dark:border-gray-700 pointer-events-auto hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors ml-2"
                        >
                            <x-heroicon-o-chevron-left class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                        </button>
                    </div>

                    <div 
                        x-show="!scrolledToEnd" 
                        x-transition.opacity 
                        class="absolute right-0 top-0 bottom-0 z-20 flex items-center pl-12 tl-fade-right pointer-events-none group-hover/container:opacity-100 transition-opacity"
                    >
                        <button 
                            @click="scroll('right')" 
                            class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-100 dark:border-gray-700 pointer-events-auto hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors mr-2"
                        >
                            <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                        </button>
                    </div>

                    {{-- Scroll Container --}}
                    <div 
                        wire:loading.class="opacity-50 transition-opacity duration-500"
                        wire:target="refresh"
                        x-ref="scrollContainer"
                        @scroll.debounce.50ms="updateScroll"
                        @mousedown="onMouseDown"
                        @mouseleave="onMouseUp"
                        @mouseup="onMouseUp"
                        @mousemove="onMouseMove"
                        :class="isDragging ? 'cursor-grabbing select-none' : 'cursor-grab'"
                        class="flex overflow-x-auto pt-4 pb-4 gap-6 snap-x snap-mandatory scrollbar-hide"
                    >
                        @forelse ($entries as $index => $entry)
                            <div class="snap-start flex-none w-72 group">
                                @if($entry->url)
                                    <a href="{{ $entry->url }}" class="block hover:no-underline" draggable="false">
                                @endif
                                <div class="flex items-center gap-3 mb-3">
                                    <div 
                                        class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white dark:bg-gray-800 ring-4 ring-white dark:ring-gray-800 shadow-sm border-2 transition-transform group-hover:scale-110"
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
                                    <div class="h-0.5 grow bg-gray-200 dark:bg-gray-700 group-last:hidden"></div>
                                    
                                    @if($entry->occurredAt)
                                        <time class="text-[10px] uppercase tracking-wider font-semibold text-gray-500 whitespace-nowrap" title="{{ $entry->occurredAt->toDateTimeString() }}">
                                            {{ $entry->occurredAt->diffForHumans() }}
                                        </time>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="pl-2">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                        {{ $entry->title }}
                                    </h4>
                                    
                                    @if ($entry->description)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">
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

                    {{-- Clustered Dots Navigation --}}
                    @if($entries->count() > 1)
                        @php
                            $globalIndex = 0;
                            $groups = $this->getGroupedEntries();
                        @endphp
                        <div class="flex justify-center flex-wrap gap-x-8 gap-y-4 mt-6 px-12">
                            @foreach ($groups as $year => $groupEntries)
                                <div class="flex flex-col items-center gap-1.5 min-w-max">
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
