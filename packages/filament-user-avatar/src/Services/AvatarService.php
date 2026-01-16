<?php

namespace BeeGoodIT\FilamentUserAvatar\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarService
{
    /**
     * Store avatar file and return the file path.
     */
    public function storeAvatar(Model $user, string $imageData, string $extension): string
    {
        $disk = $this->getDisk();
        $filename = $this->generateFilename($user, $extension);

        Storage::disk($disk)->put($filename, $imageData);

        return $filename;
    }

    /**
     * Convert base64 data URL to file and return the file path.
     */
    public function storeAvatarFromBase64(Model $user, ?string $base64DataUrl): ?string
    {
        if (in_array($base64DataUrl, [null, '', '0'], true)) {
            return null;
        }

        try {
            // Extract base64 data from data URL
            if (! str_starts_with($base64DataUrl, 'data:image/')) {
                return null;
            }

            $parts = explode(',', $base64DataUrl, 2);
            if (count($parts) !== 2) {
                return null;
            }

            $imageData = base64_decode($parts[1] ?? '');
            if ($imageData === false || ($imageData === '' || $imageData === '0')) {
                return null;
            }

            // Determine file extension
            $extension = $this->getExtensionFromDataUrl($base64DataUrl);

            return $this->storeAvatar($user, $imageData, $extension);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to process base64 avatar: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Delete the user's current avatar file.
     */
    public function deleteAvatar(Model $user): void
    {
        if (empty($user->avatar)) {
            return;
        }

        $disk = $this->getDisk();

        if (Storage::disk($disk)->exists($user->avatar)) {
            Storage::disk($disk)->delete($user->avatar);
        }
    }

    /**
     * Update user's avatar and delete the old one.
     */
    public function updateUserAvatar(Model $user, string $avatarPath): void
    {
        // Delete old avatar if it exists
        $this->deleteAvatar($user);

        // Update user record
        $user->avatar = $avatarPath;
        $user->save();
    }

    /**
     * Get the appropriate storage disk.
     */
    private function getDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }

    /**
     * Generate filename for avatar storage.
     */
    private function generateFilename(Model $user, string $extension): string
    {
        return sprintf(
            'users/%s/avatar/%s.%s',
            $user->id,
            (string) Str::uuid(),
            strtolower($extension)
        );
    }

    /**
     * Extract file extension from base64 data URL.
     */
    private function getExtensionFromDataUrl(string $dataUrl): string
    {
        if (str_contains($dataUrl, 'png')) {
            return 'png';
        }
        if (str_contains($dataUrl, 'gif')) {
            return 'gif';
        }
        if (str_contains($dataUrl, 'webp')) {
            return 'webp';
        }

        return 'jpg'; // default
    }
}
