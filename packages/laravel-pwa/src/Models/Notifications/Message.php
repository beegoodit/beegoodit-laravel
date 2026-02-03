<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStates\HasStates;
use BeegoodIT\LaravelPwa\States\Messages\MessageState;

class Message extends Model
{
    use HasUuids, \Illuminate\Database\Eloquent\Factories\HasFactory, MassPrunable, HasStates;

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
        $this->delivery_status->transitionTo(\BeegoodIT\LaravelPwa\States\Messages\OnHold::class);
    }

    public function release(): void
    {
        $this->delivery_status->transitionTo(\BeegoodIT\LaravelPwa\States\Messages\Pending::class);
    }

    public function resend(): void
    {
        $this->update(['error_message' => null]);
        $this->delivery_status->transitionTo(\BeegoodIT\LaravelPwa\States\Messages\Pending::class);
    }

    public function isOnHold(): bool
    {
        return $this->delivery_status->equals(\BeegoodIT\LaravelPwa\States\Messages\OnHold::class);
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
            'delivery_status' => MessageState::class,
        ];
    }
}
