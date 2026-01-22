<?php

namespace BeegoodIT\LaravelPwa\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }

    /**
     * Get subscription data formatted for web-push library.
     */
    public function toWebPush(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->public_key,
                'auth' => $this->auth_token,
            ],
            'contentEncoding' => $this->content_encoding,
        ];
    }

    /**
     * Scope to get subscriptions for a specific user.
     */
    protected function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Find subscription by endpoint.
     */
    public static function findByEndpoint(string $endpoint): ?self
    {
        return static::where('endpoint', $endpoint)->first();
    }
}
