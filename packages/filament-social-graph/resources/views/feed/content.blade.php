<div>
    @include('filament-social-graph::feed.partials.lightbox-overlay')

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $entity->name ?? class_basename($entity) }} - {{ __('filament-social-graph::feed.title') }}
        </h1>
    </div>

    @if($showComposer)
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedComposer::class, ['entity' => $entity, 'quillId' => $quillId])
    @endif

    <div class="mt-6">
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/filament-social-graph/js/lightbox.js') }}"></script>
    @endpush
</div>
