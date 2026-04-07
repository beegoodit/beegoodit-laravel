{{--
    Feed item sharing: Web Share API (when available), copy link, mailto, SMS.

    Usage:
        <x-filament-social-graph::feed-item-share />
        <x-filament-social-graph::feed-item-share :title="$title" :description="$metaDescription" :show-heading="false" />

    Styling: --fsg-share-accent (optional) maps to Flux’s --color-accent on this root so
    variant="primary" buttons pick up team colors. Branded pages: --fsg-share-accent: var(--team-primary).

    Expects Flux UI (flux:button, flux:icon) in the consuming app.
--}}

@props([
    'title' => null,
    'description' => null,
    'url' => null,
    'showHeading' => true,
])

@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? config('app.name');
    $shareText = filled($description) ? $description : ($title ?? $shareTitle);
    $mailtoHref = 'mailto:?subject='.rawurlencode((string) $shareTitle).'&body='.rawurlencode($shareUrl);
    $smsHref = 'sms:?&body='.rawurlencode($shareUrl);
@endphp

<div
    data-feed-item-share
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
        'class' => 'flex flex-col gap-3 [--color-accent:var(--fsg-share-accent,var(--color-primary-600))] [--color-accent-content:var(--fsg-share-accent,var(--color-primary-600))] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--fsg-share-accent,var(--color-primary-600))] dark:[--color-accent-content:var(--fsg-share-accent,var(--color-primary-600))] dark:[--color-accent-foreground:var(--color-white)]',
    ]) }}
>
    @if($showHeading)
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            {{ __('filament-social-graph::feed.share.heading') }}
        </p>
    @endif
    <div class="flex flex-col gap-3">
        <flux:button
            type="button"
            variant="primary"
            class="w-full justify-center"
            x-on:click="shareOrCopy()"
        >
            {{ __('filament-social-graph::feed.share.share') }}
        </flux:button>
        <div aria-live="polite">
            {{-- Ghost + accent border: avoids Flux outline dark:bg-zinc-700 with dark accent text (illegible). --}}
            <flux:button
                type="button"
                variant="ghost"
                class="w-full justify-center border-2 border-[color:var(--fsg-share-accent,var(--color-primary-600))] !bg-transparent text-zinc-900 hover:!bg-zinc-100 dark:border-[color:var(--fsg-share-accent,var(--color-primary-400))] dark:!bg-transparent dark:text-white dark:hover:!bg-white/10"
                x-on:click="copyLink()"
                aria-label="{{ __('filament-social-graph::feed.share.copy_link') }}"
            >
                <span
                    x-text="copied ? @js(__('filament-social-graph::feed.share.copied')) : @js(__('filament-social-graph::feed.share.copy_link'))"
                ></span>
            </flux:button>
        </div>
    </div>
    <div class="flex flex-wrap gap-x-4 gap-y-2">
        <a
            href="{{ $mailtoHref }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--fsg-share-accent,var(--color-primary-500))] rounded dark:text-zinc-200 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        >
            <flux:icon.envelope variant="outline" class="size-4 shrink-0 text-gray-600 dark:text-zinc-400" aria-hidden="true" />
            {{ __('filament-social-graph::feed.share.email') }}
        </a>
        <a
            href="{{ $smsHref }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 hover:text-[color:var(--fsg-share-accent,var(--color-primary-600))] focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--fsg-share-accent,var(--color-primary-500))] rounded dark:text-zinc-200 dark:hover:text-[color:var(--fsg-share-accent,var(--color-primary-300))]"
        >
            <flux:icon.device-phone-mobile variant="outline" class="size-4 shrink-0 text-gray-600 dark:text-zinc-400" aria-hidden="true" />
            {{ __('filament-social-graph::feed.share.sms') }}
        </a>
    </div>
</div>
