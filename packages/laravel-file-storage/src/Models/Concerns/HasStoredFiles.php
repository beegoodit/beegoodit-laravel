<?php

namespace BeegoodIT\LaravelFileStorage\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasStoredFiles
{
    /**
     * Get the storage disk to use (S3 or public based on config).
     */
    protected function getFileDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }

    /**
     * Get the public URL for a stored file.
     * Automatically generates signed URLs for S3, public URLs for local.
     */
    protected function getFileUrl(?string $path, int $expiryMinutes = 60): ?string
    {
        if (in_array($path, [null, '', '0'], true)) {
            return null;
        }

        $disk = $this->getFileDisk();

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
     * Magic method to auto-generate get{Field}Url() methods.
     * Example: If $storedFiles = ['avatar'], you can call $model->getAvatarUrl()
     */
    public function __call($method, $parameters)
    {
        // Check if calling a getXxxUrl() method
        if (preg_match('/^get(\w+)Url$/', (string) $method, $matches)) {
            $field = \Illuminate\Support\Str::snake($matches[1]);

            // Check if this field is in the storedFiles array
            if (isset($this->storedFiles) && in_array($field, $this->storedFiles)) {
                return $this->getFileUrl($this->$field, $parameters[0] ?? 60);
            }
        }

        return parent::__call($method, $parameters);
    }
}
