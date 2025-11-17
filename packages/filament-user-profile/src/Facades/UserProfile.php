<?php

namespace BeeGoodIT\FilamentUserProfile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getUserMenuItems()
 *
 * @see \BeeGoodIT\FilamentUserProfile\UserProfileHelper
 */
class UserProfile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-user-profile';
    }
}

