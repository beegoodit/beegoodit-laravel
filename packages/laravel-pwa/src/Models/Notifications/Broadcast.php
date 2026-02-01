<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    use HasUuids;

    protected $table = 'pwa_notifications_broadcasts';

    protected $fillable = [
        'trigger_type',
        'payload',
        'target_ids',
        'status',
        'total_recipients',
        'total_sent',
        'total_opened',
    ];

    protected $casts = [
        'payload' => 'array',
        'target_ids' => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
