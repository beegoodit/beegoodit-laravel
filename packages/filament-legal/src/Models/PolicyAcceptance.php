<?php

namespace BeeGoodIT\FilamentLegal\Models;

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

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LegalPolicy::class, 'legal_policy_id');
    }
}
