<?php

namespace BeegoodIT\FilamentSocialLinks\Models;

use BeegoodIT\EloquentUserstamps\HasUserStamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPlatform extends Model
{
    use HasUserStamps, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'base_url',
        'icon',
        'sort_order',
        'is_active',
    ];

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }
}
