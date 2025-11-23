<?php

namespace BeeGoodIT\EloquentUserstamps;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserStamps
{
    /**
     * Boot the trait and register model events.
     */
    protected static function bootHasUserStamps(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by_id = auth()->id();
                $model->updated_by_id = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by_id = auth()->id();
            }
        });
    }

    /**
     * Get the user who created the record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo($this->getUserModel(), 'created_by_id');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo($this->getUserModel(), 'updated_by_id');
    }

    /**
     * Get the user model class name.
     */
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }
}
