<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasUuids, \Illuminate\Database\Eloquent\Factories\HasFactory, MassPrunable;

    protected static function newFactory()
    {
        return \Database\Factories\MessageFactory::new();
    }

    protected $table = 'pwa_notifications_messages';

    protected $fillable = [
        'broadcast_id',
        'push_subscription_id',
        'notification_type',
        'data',
        'content',
        'delivery_status',
        'opened_at',
        'error_message',
    ];
    public function hold(): void
    {
        $this->update(['delivery_status' => 'on_hold']);
    }

    public function release(): void
    {
        $this->update(['delivery_status' => 'pending']);
    }

    public function isOnHold(): bool
    {
        return $this->delivery_status === 'on_hold';
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function pushSubscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    public function resolveContent(): object
    {
        // 1. If we have static content (manual broadcast), return it
        if ($this->content) {
            return (object) $this->content;
        }

        // 2. If we have a notification type, try to re-hydrate it
        if ($this->notification_type && isset($this->data['serialized'])) {
            try {
                $notification = unserialize($this->data['serialized']);

                $notifiable = $this->pushSubscription->user;
                $webPushMessage = $notification->toWebPush($notifiable);

                if (! $webPushMessage instanceof \BeegoodIT\LaravelPwa\Messages\WebPushMessage) {
                    $webPushMessage = (new \BeegoodIT\LaravelPwa\Messages\WebPushMessage)->title($webPushMessage);
                }

                return (object) $webPushMessage->toArray();
            } catch (\Exception $e) {
                return (object) [
                    'title' => 'Error resolving content',
                    'body' => $e->getMessage(),
                ];
            }
        }

        return (object) [
            'title' => '-',
            'body' => '-',
        ];
    }

    /**
     * Get the prunable query for the model.
     */
    public function prunable(): Builder
    {
        $days = config('pwa.notifications.delivery_retention_days', 30);

        return static::where('created_at', '<=', now()->subDays($days));
    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'content' => 'array',
            'opened_at' => 'datetime',
        ];
    }
}
