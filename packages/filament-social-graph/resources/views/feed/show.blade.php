@extends($layout)

@section('content')
@include('filament-social-graph::feed.partials.lightbox-overlay')
<div class="py-8 sm:py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ $feedUrl }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                {{ __('filament-social-graph::feed.back_to_feed') }}
            </a>
        </div>
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h1>
        </div>

        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedItemCard::class, [
            'feedItem' => $feedItem,
            'editRouteName' => $feedItemEditRouteName ?? null,
            'destroyRouteName' => $feedItemDestroyRouteName ?? null,
            'editRouteParams' => $feedItemEditRouteParams ?? [],
            'destroyRouteParams' => $feedItemDestroyRouteParams ?? [],
        ], key($feedItem->id))
    </div>
</div>

@push('scripts')
    <script src="{{ asset('vendor/filament-social-graph/js/lightbox.js') }}"></script>
@endpush
@endsection
