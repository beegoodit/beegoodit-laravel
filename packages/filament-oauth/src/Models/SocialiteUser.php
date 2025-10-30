<?php

namespace BeeGoodIT\FilamentOAuth\Models;

use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser as BaseSocialiteUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SocialiteUser extends BaseSocialiteUser
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';
}

