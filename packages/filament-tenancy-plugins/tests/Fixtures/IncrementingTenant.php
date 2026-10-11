<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures;

use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;
use Illuminate\Database\Eloquent\Model;

class IncrementingTenant extends Model
{
    use HasPlugins;

    protected $table = 'incrementing_tenants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];
}
