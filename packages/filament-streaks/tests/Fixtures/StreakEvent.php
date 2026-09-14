<?php

namespace BeegoodIT\FilamentStreaks\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StreakEvent extends Model
{
    use HasUuids;

    protected $table = 'streak_events';

    protected $fillable = [
        'name',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}
