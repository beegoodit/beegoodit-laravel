<flux:dropdown>
    <flux:button variant="subtle" square aria-label="Language options">
        <span class="text-xs font-bold">{{ strtoupper($currentLocale) }}</span>
    </flux:button>

    <flux:menu class="min-w-32">
        @foreach ($locales as $locale)
            <flux:menu.item href="{{ $localeUrlMap[$locale] ?? '/'.$locale }}">
                {{ $localeNameMap[$locale] ?? strtoupper($locale) }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
