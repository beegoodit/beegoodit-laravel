<?php

namespace BeegoodIT\FilamentStreaks\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserStamps
{
    protected static function bootHasUserStamps(): void
    {
        static::creating(function ($model): void {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('filament-streaks.user_model'), 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(config('filament-streaks.user_model'), 'updated_by');
    }
}
