<div>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $entity->name ?? class_basename($entity) }} - {{ __('filament-social-graph::feed.title') }}
        </h1>
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\SubscribeButton::class, ['feedOwner' => $entity])
    </div>

    @if($showComposer)
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedComposer::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
    @endif

    <div class="mt-6">
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
    </div>
</div>
