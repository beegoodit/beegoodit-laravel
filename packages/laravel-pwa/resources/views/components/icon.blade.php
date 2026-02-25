@props([
    'icon' => 'heroicon-o-home',
    'class' => 'w-5 h-5',
])

@php
    $useFilament = view()->exists('filament::components.icon') && function_exists('Filament\Support\generate_icon_html');
    $attributesBag = $useFilament ? new \Illuminate\View\ComponentAttributeBag(['class' => $class]) : null;
@endphp

@if ($useFilament && $attributesBag)
    @include('filament::components.icon', [
        'icon' => $icon,
        'alias' => null,
        'attributes' => $attributesBag,
        'size' => null,
    ])
@else
    @include('laravel-pwa::components.icon-svg', ['icon' => $icon, 'class' => $class])
@endif
