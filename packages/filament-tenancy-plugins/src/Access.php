<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins;

use BeegoodIT\FilamentTenancyPlugins\Models\PluginAssignment;
use Illuminate\Database\Eloquent\Model;

final readonly class Access
{
    public function __construct(private Model $tenant) {}

    public function has(string $id): bool
    {
        return PluginAssignment::query()
            ->where('pluginable_type', $this->tenant->getMorphClass())
            ->where('pluginable_id', (string) $this->tenant->getKey())
            ->where('plugin', $id)
            ->exists();
    }
}
