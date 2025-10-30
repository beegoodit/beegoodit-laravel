<?php

namespace BeeGoodIT\FilamentOAuth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthAccount extends Model
{
    use HasUuids;

    protected $table = 'oauth_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_tenant_id',
        'provider_email',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeWhereProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeWhereProviderId($query, string $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }
}

