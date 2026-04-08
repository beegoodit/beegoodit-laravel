<?php

namespace BeegoodIT\FilamentOpeningTimes\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slot extends Model
{
    use HasUuids;

    protected $table = 'opening_times_slots';

    protected $fillable = [
        'schedule_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'opens_at' => 'datetime:H:i:s',
            'closes_at' => 'datetime:H:i:s',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function isOvernight(): bool
    {
        $open = $this->opens_at instanceof \Carbon\CarbonInterface ? $this->opens_at->format('H:i:s') : (string) $this->opens_at;
        $close = $this->closes_at instanceof \Carbon\CarbonInterface ? $this->closes_at->format('H:i:s') : (string) $this->closes_at;

        return $close < $open;
    }
}
