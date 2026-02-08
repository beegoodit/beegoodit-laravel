<?php

namespace BeegoodIT\FilamentLegal\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyAcceptance extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'legal_policy_id',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LegalPolicy::class, 'legal_policy_id');
    }

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }
}
