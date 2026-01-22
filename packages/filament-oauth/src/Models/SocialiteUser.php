<?php

namespace BeegoodIT\FilamentOAuth\Models;

use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser as BaseSocialiteUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SocialiteUser extends BaseSocialiteUser
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Get the user instance, ensuring teams relationship is loaded.
     * This is critical for OAuth registration where teams are assigned
     * during user creation and need to be available for tenant checks.
     */
    public function getUser(): Authenticatable
    {
        $user = parent::getUser();

        // Ensure teams relationship is loaded if not already loaded
        // This is critical for Filament's getTenants() method
        if (! $user->relationLoaded('teams')) {
            $user->load('teams');
        }

        return $user;
    }
}
