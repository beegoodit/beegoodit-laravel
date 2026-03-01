<div>
    <h1 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
        {{ __('filament-social-graph::feed.home') }}
    </h1>

    @if($showComposer)
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedComposer::class)
    @endif

    <div class="mt-6">
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class)
    </div>
</div>
