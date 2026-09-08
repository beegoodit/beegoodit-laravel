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
<din-5008 @if ($form === 'B') form="B" @endif @if ($noMarkers) no-markers @endif @if ($debugLayout) debug-layout @endif>
    <div class="briefkopf">{!! $briefkopfHtml !!}</div>
    <div class="rucksendeangabe">{!! $rucksendeangabeHtml !!}</div>
    <div class="vermerkzone">{!! $vermerkHtml !!}</div>
    <div class="anschriftzone">{!! $anschriftHtml !!}</div>
    <div class="informationsblock">{!! $informationsblockHtml !!}</div>
    <div class="textfeld">{!! $textfeldHtml !!}</div>
</din-5008>
</body>
</html>
