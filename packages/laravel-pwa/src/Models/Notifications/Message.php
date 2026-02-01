<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasUuids, MassPrunable;

    protected $table = 'pwa_notifications_messages';

    protected $fillable = [
        'broadcast_id',
        'push_subscription_id',
        'content',
        'delivery_status',
        'opened_at',
        'error_message',
    ];

    protected $casts = [
        'content' => 'array',
        'opened_at' => 'datetime',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function pushSubscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    /**
     * Get the prunable query for the model.
     */
    public function prunable(): Builder
    {
        $days = config('pwa.notifications.delivery_retention_days', 30);

        return static::where('created_at', '<=', now()->subDays($days));
    }
}
