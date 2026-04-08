<?php

namespace BeegoodIT\FilamentOpeningTimes\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Schedule extends Model
{
    use HasUuids;

    protected $table = 'opening_times_schedules';

    protected $fillable = [
        'openable_type',
        'openable_id',
        'timezone',
        'active_from',
        'active_to',
    ];

    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    public function openable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<Slot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class, 'schedule_id')->orderBy('day_of_week')->orderBy('sort_order');
    }

    public function isActiveAt(\Carbon\CarbonInterface $at): bool
    {
        return $at->greaterThanOrEqualTo($this->active_from) && $at->lessThanOrEqualTo($this->active_to);
    }
}
