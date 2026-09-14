<?php

namespace BeegoodIT\FilamentStreaks\Models;

use BeegoodIT\FilamentStreaks\Database\Factories\StreakFactory;
use BeegoodIT\FilamentStreaks\Models\Concerns\HasUserStamps;
use BeegoodIT\FilamentStreaks\Support\StreakCurrentCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Streak extends Model
{
    /** @use HasFactory<StreakFactory> */
    use HasFactory, HasUserStamps, HasUuids;

    protected $table = 'beegoodit_filament_streaks_streaks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'strategy_identifier',
        'subject_type',
        'subject_id',
        'scope_type',
        'scope_id',
        'start_at',
        'end_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'date',
            'end_at' => 'date',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('end_at');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeEnded(Builder $query): Builder
    {
        return $query->whereNotNull('end_at');
    }

    public function isOpen(): bool
    {
        return $this->end_at === null;
    }

    public function currentCount(): int
    {
        return app(StreakCurrentCalculator::class)->calculate($this);
    }

    protected static function booted(): void
    {
        static::creating(function (Streak $streak): void {
            if ($streak->end_at !== null) {
                return;
            }

            $exists = static::query()
                ->open()
                ->where('strategy_identifier', $streak->strategy_identifier)
                ->where('subject_type', $streak->subject_type)
                ->where('subject_id', $streak->subject_id)
                ->when(
                    $streak->scope_type === null,
                    fn (Builder $query) => $query->whereNull('scope_type')->whereNull('scope_id'),
                    fn (Builder $query) => $query
                        ->where('scope_type', $streak->scope_type)
                        ->where('scope_id', $streak->scope_id),
                )
                ->exists();

            if ($exists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'strategy_identifier' => __('filament-streaks::streak.validation.open_run_exists'),
                ]);
            }
        });
    }

    protected static function newFactory(): StreakFactory
    {
        return StreakFactory::new();
    }
}
