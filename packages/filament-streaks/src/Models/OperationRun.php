<?php

namespace BeegoodIT\FilamentStreaks\Models;

use BeegoodIT\FilamentStreaks\Database\Factories\OperationRunFactory;
use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationRun extends Model
{
    /** @use HasFactory<OperationRunFactory> */
    use HasFactory, HasUuids;

    public const TYPE_STREAK_HISTORY = 'streaks.history';

    protected $table = 'beegoodit_filament_streaks_operation_runs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'params',
        'status',
        'result',
        'error',
        'requested_by',
        'started_at',
        'finished_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'params' => 'array',
            'result' => 'array',
            'status' => OperationRunStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(config('filament-streaks.user_model'), 'requested_by');
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeHistory(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_STREAK_HISTORY);
    }

    protected static function newFactory(): OperationRunFactory
    {
        return OperationRunFactory::new();
    }
}
