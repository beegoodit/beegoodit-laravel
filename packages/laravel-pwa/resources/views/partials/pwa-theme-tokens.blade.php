@php
    use BeegoodIT\LaravelPwa\Support\PwaThemeTokens;

    $lightBlock = PwaThemeTokens::renderBlock(':root', PwaThemeTokens::forScope('light'));
    $darkBlock = PwaThemeTokens::renderBlock('.dark', PwaThemeTokens::forScope('dark'));
@endphp

@if ($lightBlock !== '' || $darkBlock !== '')
    <style>
        {!! $lightBlock !!}

        {!! $darkBlock !!}
    </style>
@endif
