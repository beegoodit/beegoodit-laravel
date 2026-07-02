@php
    $defaultThemeMode = $defaultThemeMode ?? 'system';
@endphp

<style>
    :root {
        --default-theme-mode: {{ $defaultThemeMode }};
    }
</style>

<script>
    (function () {
        const defaultTheme = getComputedStyle(document.documentElement)
            .getPropertyValue('--default-theme-mode')
            .trim() || @js($defaultThemeMode);

        const theme = localStorage.getItem('theme') ?? defaultTheme;

        if (
            theme === 'dark' ||
            (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>
