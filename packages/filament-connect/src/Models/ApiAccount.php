<?php

namespace Beegoodit\FilamentConnect\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApiAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'service',
        'name',
        'credentials',
        'is_active',
        'owner_id',
        'owner_type',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:json',
            'is_active' => 'boolean',
        ];
    }
}
