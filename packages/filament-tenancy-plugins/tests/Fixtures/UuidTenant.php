<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures;

use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UuidTenant extends Model
{
    use HasPlugins, HasUuids;

    protected $table = 'uuid_tenants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];
}
