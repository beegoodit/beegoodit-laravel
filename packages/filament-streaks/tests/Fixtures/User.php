<?php

namespace BeegoodIT\FilamentStreaks\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'is_admin',
    ];

    protected $hidden = [
        'password',
    ];

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
