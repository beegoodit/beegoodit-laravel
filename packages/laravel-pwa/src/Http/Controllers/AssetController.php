<?php

namespace BeegoodIT\LaravelPwa\Http\Controllers;

use BeegoodIT\LaravelPwa\Support\PwaAssets;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class AssetController
{
    public function __invoke(string $asset): Response
    {
        $path = PwaAssets::path($asset);

        abort_unless(File::exists($path), 404);

        return response(File::get($path), 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
