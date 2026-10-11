<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentSplitButton;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class FilamentSplitButtonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-split-button');

        $cssPath = __DIR__.'/../resources/css/split-button.css';

        // Path-repo packages keep a stable Composer version (e.g. dev-branch),
        // so Filament's ?v= query never changes when CSS is edited. Bust with mtime.
        FilamentAsset::register([
            Css::make('split-button', $cssPath)
                ->html(new HtmlString(sprintf(
                    '<link href="%s?v=%s" rel="stylesheet" data-navigate-track />',
                    asset('css/beegoodit/filament-split-button/split-button.css'),
                    (string) filemtime($cssPath),
                ))),
        ], 'beegoodit/filament-split-button');
    }
}
