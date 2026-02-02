<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    use HasUuids, \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\BroadcastFactory::new();
    }

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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'target_ids' => 'array',
        ];
    }
}
