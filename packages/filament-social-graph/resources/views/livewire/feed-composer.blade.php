<div>
    @auth
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <form wire:submit="submit" class="space-y-4">
            <flux:field>
                <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.subject') }}</flux:label>
                <flux:input
                    wire:model="subject"
                    id="feed-subject"
                    placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                />
            </flux:field>
            <flux:field>
                <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.body') }}</flux:label>
                <flux:textarea
                    wire:model="body"
                    id="feed-body"
                    rows="3"
                    placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"
                />
            </flux:field>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:field>
                    <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.visibility') }}</flux:label>
                    <flux:select wire:model="visibility" class="min-w-[10rem]">
                        @foreach (\BeegoodIT\FilamentSocialGraph\Enums\Visibility::cases() as $v)
                            <flux:select.option :value="$v->value">{{ $v->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:button type="submit" variant="primary" size="base">
                    {{ __('filament-social-graph::feed.post') }}
                </flux:button>
            </div>
        </form>
    </div>
    @endauth
</div>
