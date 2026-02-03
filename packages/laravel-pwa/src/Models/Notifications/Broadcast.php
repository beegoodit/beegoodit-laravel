<?php

namespace BeegoodIT\LaravelPwa\Models\Notifications;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;
use BeegoodIT\LaravelPwa\States\Broadcasts\BroadcastState;

class Broadcast extends Model
{
    use HasUuids, \Illuminate\Database\Eloquent\Factories\HasFactory, HasStates;

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

    public function hold(): void
    {
        $this->status->transitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\OnHold::class);

        $this->messages()
            ->where('delivery_status', \BeegoodIT\LaravelPwa\States\Messages\Pending::$name)
            ->get()
            ->each->hold();
    }

    public function release(): void
    {
        $this->status->transitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class);

        $this->messages()
            ->where('delivery_status', \BeegoodIT\LaravelPwa\States\Messages\OnHold::$name)
            ->get()
            ->each->release();
    }

    public function resend(): void
    {
        $this->status->transitionTo(\BeegoodIT\LaravelPwa\States\Broadcasts\Pending::class);

        $this->messages()
            ->whereIn('delivery_status', [
                \BeegoodIT\LaravelPwa\States\Messages\Sent::$name,
                \BeegoodIT\LaravelPwa\States\Messages\Failed::$name,
            ])
            ->get()
            ->each->resend();
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'target_ids' => 'array',
            'status' => BroadcastState::class,
        ];
    }
}
