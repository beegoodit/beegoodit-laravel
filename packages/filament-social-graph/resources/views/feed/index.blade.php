@extends($layout)

@section('content')
@include('filament-social-graph::feed.content', ['entity' => $entity, 'feed' => $feed ?? null, 'showComposer' => $showComposer, 'quillId' => $quillId])
@endsection
