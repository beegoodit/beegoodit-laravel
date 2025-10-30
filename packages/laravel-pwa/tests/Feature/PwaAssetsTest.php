<?php

it('manifest file exists', function () {
    $manifest = file_get_contents(__DIR__ . '/../../public/manifest.json');
    $data = json_decode($manifest, true);
    
    expect($data)->toHaveKey('name');
    expect($data)->toHaveKey('short_name');
    expect($data)->toHaveKey('icons');
});

it('service worker file exists', function () {
    $sw = file_get_contents(__DIR__ . '/../../public/sw.js');
    
    expect($sw)->toContain('CACHE_NAME');
    expect($sw)->toContain('addEventListener');
});

it('pwa meta view exists', function () {
    $view = file_get_contents(__DIR__ . '/../../resources/views/partials/pwa-meta.blade.php');
    
    expect($view)->toContain('manifest.json');
    expect($view)->toContain('serviceWorker');
});

