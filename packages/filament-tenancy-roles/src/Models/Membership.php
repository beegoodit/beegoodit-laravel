<?php

namespace BeegoodIT\FilamentTenancyRoles\Models;

use BeegoodIT\FilamentTenancyRoles\Enums\TeamRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Membership extends Pivot
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Membership $membership) {
            if ($membership->role === null) {
                $membership->role = TeamRole::Member;
            }
        });
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
        ];
    }
}
