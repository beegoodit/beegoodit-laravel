<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use Illuminate\Support\Facades\Cache;

class PromptRateLimiter
{
    public function tooManyAttempts(int|string|null $tenantId, int|string $userId): bool
    {
        $limit = (int) config('filament-ai.rate_limit.daily_per_user', 200);

        if ($limit <= 0) {
            return false;
        }

        $count = (int) Cache::get($this->cacheKey($tenantId, $userId), 0);

        return $count >= $limit;
    }

    public function hit(int|string|null $tenantId, int|string $userId): void
    {
        $limit = (int) config('filament-ai.rate_limit.daily_per_user', 200);

        if ($limit <= 0) {
            return;
        }

        $key = $this->cacheKey($tenantId, $userId);
        $count = (int) Cache::get($key, 0);

        Cache::put($key, $count + 1, now()->endOfDay());
    }

    protected function cacheKey(int|string|null $tenantId, int|string $userId): string
    {
        return 'filament-ai:prompts:'.($tenantId ?? 'global').':'.$userId.':'.now()->toDateString();
    }
}
