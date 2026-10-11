<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Events;

use Illuminate\Database\Eloquent\Model;

class PluginAssigned
{
    public function __construct(
        public Model $tenant,
        public string $plugin,
    ) {}
}
