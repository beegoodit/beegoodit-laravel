<?php

namespace BeegoodIT\LaravelPwa\Support;

use Illuminate\Support\Facades\File;

class PwaAssets
{
    /** @var list<string> */
    public const ALLOWED_ASSETS = [
        'pwa-nav.css',
        'push-prompt.css',
    ];

    public static function url(string $asset): string
    {
        if (! in_array($asset, self::ALLOWED_ASSETS, true)) {
            throw new \InvalidArgumentException("Unsupported PWA asset [{$asset}].");
        }

        $path = self::path($asset);
        $version = File::exists($path) ? (string) File::lastModified($path) : '0';

        $publishedPath = public_path('css/'.$asset);

        if (File::exists($publishedPath)) {
            return asset('css/'.$asset).'?v='.$version;
        }

        return route('laravel-pwa.assets', ['asset' => $asset]).'?v='.$version;
    }

    public static function path(string $asset): string
    {
        if (! in_array($asset, self::ALLOWED_ASSETS, true)) {
            throw new \InvalidArgumentException("Unsupported PWA asset [{$asset}].");
        }

        $publishedPath = public_path('css/'.$asset);

        if (File::exists($publishedPath)) {
            return $publishedPath;
        }

        return dirname(__DIR__, 2).'/resources/css/'.$asset;
    }
}
