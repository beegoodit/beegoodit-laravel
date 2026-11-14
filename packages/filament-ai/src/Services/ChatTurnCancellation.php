<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use Illuminate\Support\Facades\Cache;

class ChatTurnCancellation
{
    public function begin(string $turnId, int|string $userId): void
    {
        Cache::put($this->key($turnId), [
            'user_id' => $userId,
            'stop' => false,
        ], now()->addMinutes(30));
    }

    public function requestStop(string $turnId, int|string $userId): bool
    {
        $payload = Cache::get($this->key($turnId));

        if (! is_array($payload) || (string) ($payload['user_id'] ?? '') !== (string) $userId) {
            return false;
        }

        $payload['stop'] = true;
        Cache::put($this->key($turnId), $payload, now()->addMinutes(30));

        return true;
    }

    public function shouldStop(string $turnId): bool
    {
        $payload = Cache::get($this->key($turnId));

        return is_array($payload) && ($payload['stop'] ?? false) === true;
    }

    public function forget(string $turnId): void
    {
        Cache::forget($this->key($turnId));
    }

    protected function key(string $turnId): string
    {
        return 'filament-ai:turn:'.$turnId;
    }
}
