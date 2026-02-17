<?php

namespace BeegoodIT\FilamentLegal\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LegalIdentity extends Model
{
    use HasFactory, HasUuids;

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
        'founded_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'founded_at' => 'date',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): \BeegoodIT\FilamentLegal\Database\Factories\LegalIdentityFactory
    {
        return \BeegoodIT\FilamentLegal\Database\Factories\LegalIdentityFactory::new();
    }
}
