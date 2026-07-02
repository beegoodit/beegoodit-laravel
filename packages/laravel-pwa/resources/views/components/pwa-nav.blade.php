@props([
    'items' => [],
    'menuTitle' => 'Menu',
])

@php
    $items = is_array($items) ? $items : (is_callable($items) ? $items() : []);
    $paddingBottom = config('pwa.navigation.padding_bottom', '4rem');

    $legacyBarClass = config('pwa.navigation.bar_class');
    $legacySheetPanelClass = config('pwa.navigation.sheet_panel_class');
    $legacySheetBackdropClass = config('pwa.navigation.sheet_backdrop_class');
    $legacySheetHeaderBorderClass = config('pwa.navigation.sheet_header_border_class');
    $legacySheetTitleClass = config('pwa.navigation.sheet_title_class');
    $legacySheetCloseClass = config('pwa.navigation.sheet_close_class');
    $legacyActiveClass = config('pwa.navigation.active_color_class');
    $legacyInactiveClass = trim((string) config('pwa.navigation.bar_item_inactive_class', '').' '.(string) config('pwa.navigation.bar_item_hover_class', ''));
@endphp

<div
    class="pwa-nav pwa-nav--with-padding"
    style="--pwa-nav-padding-bottom: {{ $paddingBottom }}"
    x-data="{ mobileMenuOpen: false }"
>
    <div
        id="pwa-navigation-bar"
        class="pwa-nav__bar {{ $legacyBarClass }}"
    >
        <nav class="pwa-nav__bar-inner" aria-label="{{ $menuTitle }}">
            @foreach ($items as $item)
                @php
                    $isActive = $item['active'] ?? false;
                    $isMenu = isset($item['action']) && $item['action'] === 'toggleMenu';
                @endphp

                @if ($isMenu)
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="pwa-nav__item {{ $legacyInactiveClass }}"
                        :class="{ 'pwa-nav__item--active': mobileMenuOpen }"
                    >
                        <span class="pwa-nav__item-icon">
                            @include('laravel-pwa::components.icon', ['icon' => $item['icon'] ?? 'heroicon-o-bars-3', 'class' => 'pwa-nav__icon'])
                        </span>
                        <span class="pwa-nav__item-label">
                            {{ $item['label'] ?? $menuTitle }}
                        </span>
                    </button>
                @else
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        @class([
                            'pwa-nav__item',
                            'pwa-nav__item--active' => $isActive,
                            $legacyActiveClass => $isActive,
                            $legacyInactiveClass => ! $isActive,
                        ])
                    >
                        <span class="pwa-nav__item-icon">
                            @include('laravel-pwa::components.icon', [
                                'icon' => $item['icon'] ?? 'heroicon-o-home',
                                'class' => 'pwa-nav__icon',
                            ])
                        </span>
                        <span class="pwa-nav__item-label">
                            {{ $item['label'] ?? '' }}
                        </span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>

    <div
        x-show="mobileMenuOpen"
        x-cloak
        class="pwa-nav__overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="pwa-nav-sheet-title"
    >
        <div
            x-show="mobileMenuOpen"
            x-transition:enter-start="pwa-nav__backdrop-enter-start"
            x-transition:enter-end="pwa-nav__backdrop-enter-end"
            x-transition:leave-start="pwa-nav__backdrop-leave-start"
            x-transition:leave-end="pwa-nav__backdrop-leave-end"
            class="pwa-nav__backdrop {{ $legacySheetBackdropClass }}"
            @click="mobileMenuOpen = false"
        ></div>

        <div
            x-show="mobileMenuOpen"
            x-transition:enter-start="pwa-nav__sheet-enter-start"
            x-transition:enter-end="pwa-nav__sheet-enter-end"
            x-transition:leave-start="pwa-nav__sheet-leave-start"
            x-transition:leave-end="pwa-nav__sheet-leave-end"
            class="pwa-nav__sheet {{ $legacySheetPanelClass }}"
        >
            <div class="pwa-nav__sheet-header {{ $legacySheetHeaderBorderClass }}">
                <span id="pwa-nav-sheet-title" class="pwa-nav__sheet-title {{ $legacySheetTitleClass }}">{{ $menuTitle }}</span>
                <button
                    type="button"
                    class="pwa-nav__sheet-close {{ $legacySheetCloseClass }}"
                    @click="mobileMenuOpen = false"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="pwa-nav__sheet-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="pwa-nav__sheet-body">
                {{ $menu ?? ($slot ?? '') }}
            </div>
        </div>
    </div>
</div>
