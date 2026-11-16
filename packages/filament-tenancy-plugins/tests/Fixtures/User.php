<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures;

use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasPlugins;

    protected $table = 'users';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
    ];
}
