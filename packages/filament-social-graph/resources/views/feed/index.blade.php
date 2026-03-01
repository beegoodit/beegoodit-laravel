@extends($layout)

@section('content')
@include('filament-social-graph::feed.content', ['entity' => $entity, 'showComposer' => $showComposer])
@endsection
