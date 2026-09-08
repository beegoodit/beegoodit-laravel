<!DOCTYPE html>
<html lang="und">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 0; }
        html, body { margin: 0; padding: 0; }
        @include('laravel-pdf-renderer::din5008.partials.css')
    </style>
</head>
<body>
<din-5008-folgeseite @if ($debugLayout) debug-layout @endif>
    <div class="briefkopf">
        @if ($logoDataUri)
            <img src="{{ $logoDataUri }}" alt="">
        @endif
    </div>
    <div class="textfeld body">{!! $bodyHtml !!}</div>
    <div class="seitenangabe"></div>
</din-5008-folgeseite>
</body>
</html>
