<?php

namespace BeegoodIT\FilamentLegal\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LegalIdentity extends Model
{
    use HasUuids;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'form',
        'representative',
        'email',
        'phone',
        'vat_id',
        'register_court',
        'register_number',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
