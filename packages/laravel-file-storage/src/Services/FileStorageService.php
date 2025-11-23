<?php

namespace BeeGoodIT\LaravelFileStorage\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Store a file and return its path.
     */
    public function store(string $contents, string $directory, ?string $filename = null, ?string $disk = null): string
    {
        $disk = $disk ?? $this->getDefaultDisk();
        $filename = $filename ?? Str::uuid().'.bin';
        $path = trim($directory, '/').'/'.$filename;

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    /**
     * Delete a file if it exists.
     */
    public function delete(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getDefaultDisk();

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Check if a file exists.
     */
    public function exists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getDefaultDisk();

        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get the URL for a file.
     */
    public function url(string $path, int $expiryMinutes = 60, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->getDefaultDisk();

        if (! $this->exists($path, $disk)) {
            return null;
        }

        // For S3, use temporary signed URLs
        if ($disk === 's3') {
            return Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes($expiryMinutes)
            );
        }

        // For local/public, use direct URLs
        return Storage::disk($disk)->url($path);
    }

    /**
     * Get the default disk to use.
     */
    protected function getDefaultDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }
}
