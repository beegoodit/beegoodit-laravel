@extends($layout)

@section('content')
<div>
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
@endsection
