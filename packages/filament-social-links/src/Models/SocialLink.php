<?php

namespace BeegoodIT\FilamentSocialLinks\Models;

use BeegoodIT\EloquentUserstamps\HasUserStamps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialLink extends Model
{
    use HasUserStamps, HasUuids;

    protected $fillable = [
        'linkable_id',
        'linkable_type',
        'social_platform_id',
        'handle',
        'sort_order',
    ];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class, 'social_platform_id');
    }

    /**
     * Get the full URL for this social link.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $baseUrl = (string) $this->platform->base_url;
                $handle = ltrim($this->handle, '/@');

                if (str_ends_with($baseUrl, '@')) {
                    return $baseUrl.$handle;
                }

                return rtrim($baseUrl, '/').'/'.$handle;
            }
        );
    }
}
