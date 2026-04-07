{{--
    Compact share actions for a single feed item (footer row: Web Share, copy link, mailto, SMS).

    Expects Flux UI (flux:button, flux:icon). Styling: --fsg-share-accent (optional), same as feed-item-share.

    @props url (required), title, description
--}}

@props([
    'url',
    'title' => null,
    'description' => null,
])

@php
    $shareUrl = $url;
    $shareTitle = $title ?? config('app.name');
    $shareText = filled($description) ? $description : ($title ?? $shareTitle);
    $mailtoHref = 'mailto:?subject='.rawurlencode((string) $shareTitle).'&body='.rawurlencode($shareUrl);
    $smsHref = 'sms:?&body='.rawurlencode($shareUrl);
@endphp

<div
    data-feed-item-share-inline
    x-data="{
        permalink: @js($shareUrl),
        shareTitle: @js($shareTitle),
        shareText: @js($shareText),
        copied: false,
        copyTimer: null,
        async shareOrCopy() {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: this.shareTitle,
                        text: this.shareText,
                        url: this.permalink,
                    });
                } catch (e) {
                    if (e && e.name === 'AbortError') {
                        return;
                    }
                    await this.copyLink();
                }
            } else {
                await this.copyLink();
            }
        },
        copyViaExecCommand(text) {
            try {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.top = '0';
                ta.style.left = '0';
                ta.style.width = '2em';
                ta.style.height = '2em';
                ta.style.padding = '0';
                ta.style.border = 'none';
                ta.style.outline = 'none';
                ta.style.boxShadow = 'none';
                ta.style.background = 'transparent';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(ta);

                return ok;
            } catch (err) {
                return false;
            }
        },
        async copyLink() {
            let ok = false;
            try {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    await navigator.clipboard.writeText(this.permalink);
                    ok = true;
                }
            } catch (e) {
                ok = false;
            }
            if (! ok) {
                ok = this.copyViaExecCommand(this.permalink);
            }
            if (ok) {
                this.copied = true;
                if (this.copyTimer) {
                    clearTimeout(this.copyTimer);
                }
                this.copyTimer = setTimeout(() => { this.copied = false }, 2000);
            } else {
                console.error('Feed share: could not copy link');
            }
        },
    }"
    {{ $attributes->merge([
        'class' => 'mt-4 flex flex-wrap items-center justify-end gap-1 border-t border-gray-200 pt-3 dark:border-gray-700 [--color-accent:var(--fsg-share-accent,var(--color-primary-600))] [--color-accent-content:var(--fsg-share-accent,var(--color-primary-600))] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--fsg-share-accent,var(--color-primary-600))] dark:[--color-accent-content:var(--fsg-share-accent,var(--color-primary-600))] dark:[--color-accent-foreground:var(--color-white)]',
    ]) }}
>
    <span class="sr-only" aria-live="polite" x-text="copied ? @js(__('filament-social-graph::feed.share.copied')) : ''"></span>
    <flux:button
        type="button"
        variant="ghost"
        size="sm"
        square
        class="text-gray-600 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] dark:text-zinc-400 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        x-on:click="shareOrCopy()"
        title="{{ __('filament-social-graph::feed.share.share') }}"
        aria-label="{{ __('filament-social-graph::feed.share.share') }}"
    >
        <flux:icon.arrow-up-tray variant="outline" class="size-5 shrink-0" aria-hidden="true" />
    </flux:button>
    {{-- Native button so browser :title tooltip binds to the hovered element (Flux button may not forward title). --}}
    <button
        type="button"
        class="inline-flex size-9 shrink-0 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--fsg-share-accent,var(--color-primary-500))] dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        x-on:click="copyLink()"
        x-bind:title="copied ? @js(__('filament-social-graph::feed.share.copied')) : @js(__('filament-social-graph::feed.share.copy_link'))"
        x-bind:aria-label="copied ? @js(__('filament-social-graph::feed.share.copied')) : @js(__('filament-social-graph::feed.share.copy_link'))"
    >
        <flux:icon.link variant="outline" class="size-5 shrink-0" aria-hidden="true" />
    </button>
    <a
        href="{{ $mailtoHref }}"
        class="inline-flex size-9 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--fsg-share-accent,var(--color-primary-500))] dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        title="{{ __('filament-social-graph::feed.share.email') }}"
    >
        <flux:icon.envelope variant="outline" class="size-5 shrink-0" aria-hidden="true" />
        <span class="sr-only">{{ __('filament-social-graph::feed.share.email') }}</span>
    </a>
    <a
        href="{{ $smsHref }}"
        class="inline-flex size-9 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--fsg-share-accent,var(--color-primary-500))] dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        title="{{ __('filament-social-graph::feed.share.sms') }}"
    >
        <flux:icon.device-phone-mobile variant="outline" class="size-5 shrink-0" aria-hidden="true" />
        <span class="sr-only">{{ __('filament-social-graph::feed.share.sms') }}</span>
    </a>
</div>
