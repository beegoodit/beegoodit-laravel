<?php

namespace BeeGoodIT\FilamentLegal\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'version',
        'content',
        'is_active',
        'published_at',
    ];

    public function acceptances(): HasMany
    {
        return $this->hasMany(PolicyAcceptance::class);
    }

    public static function getActive(string $type): ?self
    {
        return self::where('type', $type)
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first();
    }
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
