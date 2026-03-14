<div class="space-y-4" wire:poll.keep-alive>
    @forelse($feedItems as $feedItem)
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedItemCard::class, [
            'feedItem' => $feedItem,
            'editRouteName' => $editRouteName,
            'destroyRouteName' => $destroyRouteName,
            'editRouteParams' => $editRouteParams ?? [],
            'destroyRouteParams' => $destroyRouteParams ?? [],
        ], key($feedItem->id))
    @empty
        <p class="text-gray-500 dark:text-gray-400">
            {{ __('filament-social-graph::feed.no_items') }}
        </p>
    @endforelse

    @if($feedItems->hasPages())
        <div class="mt-4">
            {{ $feedItems->links() }}
        </div>
    @endif
</div>
