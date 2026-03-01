@auth
<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <form wire:submit="submit" class="space-y-3">
        <div>
            <label for="feed-subject" class="sr-only">{{ __('filament-social-graph::feed_item.subject') }}</label>
            <input type="text" wire:model="subject" id="feed-subject" placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>
        <div>
            <label for="feed-body" class="sr-only">{{ __('filament-social-graph::feed_item.body') }}</label>
            <textarea wire:model="body" id="feed-body" rows="3" placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
        </div>
        <div class="flex items-center justify-between">
            <select wire:model="visibility" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @foreach(\BeegoodIT\FilamentSocialGraph\Enums\Visibility::cases() as $v)
                    <option value="{{ $v->value }}">{{ $v->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                {{ __('filament-social-graph::feed.post') }}
            </button>
        </div>
    </form>
</div>
@endauth
