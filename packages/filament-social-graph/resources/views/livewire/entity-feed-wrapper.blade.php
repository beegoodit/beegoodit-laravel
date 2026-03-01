<div {{ $attributes->merge(['class' => 'filament-social-graph-entity-feed']) }}>
    @if(isset($entity))
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\EntityFeedPage::class, ['entity' => $entity])
    @else
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedPage::class)
    @endif
</div>
