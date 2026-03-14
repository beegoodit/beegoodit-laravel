<div class="py-8 sm:py-12">
    @include('filament-social-graph::feed.partials.lightbox-overlay')

    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $entity->name ?? class_basename($entity) }} - {{ __('filament-social-graph::feed.title') }}
            </h1>
        </div>

        @if($showComposer)
            @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedCreateForm::class, ['entity' => $entity, 'quillId' => $quillId])
        @endif

        <div class="mt-6">
            @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class, [
                'feedId' => $feed?->getKey(),
                'entityType' => $entity->getMorphClass(),
                'entityId' => $entity->getKey(),
            ])
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/filament-social-graph/js/lightbox.js') }}"></script>
    @endpush
</div>
