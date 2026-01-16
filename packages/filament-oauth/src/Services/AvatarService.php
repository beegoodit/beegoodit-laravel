<?php

namespace BeeGoodIT\FilamentOAuth\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarService
{
    /**
     * Sync avatar from OAuth user to local user model.
     */
    public function syncAvatar(Model $user, $oauthUser): void
    {
        $avatarUrl = $oauthUser->getAvatar();

        if (!$avatarUrl) {
            return;
        }

        try {
            $response = Http::get($avatarUrl);

            if ($response->successful()) {
                $extension = pathinfo(parse_url((string) $avatarUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'avatars/' . Str::uuid() . '.' . $extension;

                $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
                Storage::disk($disk)->put($filename, $response->body());

                // Delete old avatar if exists
                if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
                    Storage::disk($disk)->delete($user->avatar);
                }

                $user->update(['avatar' => $filename]);

                Log::info('Synced avatar for user', [
                    'user_id' => $user->id,
                    'avatar' => $filename,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync avatar for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
