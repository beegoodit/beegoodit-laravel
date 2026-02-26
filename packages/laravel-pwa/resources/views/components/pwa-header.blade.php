@php
    $headerClass = config('pwa.header.header_class', 'fixed top-0 inset-x-0 z-[105] bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm');
    $paddingTop = config('pwa.header.padding_top', '5rem');
@endphp

<style>
    #pwa-header {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
    }
    main,
    .fi-main,
    .fi-sidebar {
        padding-top: {{ $paddingTop }} !important;
    }
</style>

<header
    id="pwa-header"
    class="{{ $headerClass }}"
    style="padding-top: env(safe-area-inset-top, 0px);"
>
    {{ $slot }}
</header>
