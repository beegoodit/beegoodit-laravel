<?php

namespace BeegoodIT\FilamentStreaks\Models;

use BeegoodIT\FilamentStreaks\Database\Factories\QualificationFactory;
use BeegoodIT\FilamentStreaks\Models\Concerns\HasUserStamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Qualification extends Model
{
    /** @use HasFactory<QualificationFactory> */
    use HasFactory, HasUserStamps, HasUuids;

    protected $table = 'beegoodit_filament_streaks_qualifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'streak_id',
        'event_type',
        'event_id',
        'happened_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
        ];
    }

    public function streak(): BelongsTo
    {
        return $this->belongsTo(Streak::class);
    }

    public function event(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): QualificationFactory
    {
        return QualificationFactory::new();
    }
}
