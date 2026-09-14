<?php

namespace BeegoodIT\FilamentStreaks\Models;

use BeegoodIT\FilamentStreaks\Database\Factories\NotificationFactory;
use BeegoodIT\FilamentStreaks\Models\Concerns\HasUserStamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory, HasUserStamps, HasUuids;

    public const KIND_AT_RISK = 'at_risk';

    public const KIND_BROKEN = 'broken';

    protected $table = 'beegoodit_filament_streaks_notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'streak_id',
        'period_key',
        'kind',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function streak(): BelongsTo
    {
        return $this->belongsTo(Streak::class);
    }

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }
}
