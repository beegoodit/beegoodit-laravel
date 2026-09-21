<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PluginAssignment extends Model
{
    protected $table = 'beegoodit_filament_tenancy_plugins_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pluginable_type',
        'pluginable_id',
        'plugin',
    ];

    public function pluginable(): MorphTo
    {
        return $this->morphTo();
    }
}
