@extends($layout)

@section('content')
<div class="py-8 sm:py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h1>
        </div>

        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedEditForm::class, [
            'feedItem' => $feedItem,
            'feedUrl' => $feedUrl,
        ])
    </div>
</div>
@endsection
