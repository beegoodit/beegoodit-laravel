<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Models\Concerns;

use BeegoodIT\FilamentTenancyPlugins\Events\PluginAssigned;
use BeegoodIT\FilamentTenancyPlugins\Events\PluginRevoked;
use BeegoodIT\FilamentTenancyPlugins\Models\PluginAssignment;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\UniqueConstraintViolationException;
use InvalidArgumentException;

trait HasPlugins
{
    public function pluginAssignments(): MorphMany
    {
        return $this->morphMany(PluginAssignment::class, 'pluginable');
    }

    public function hasPlugin(string $id): bool
    {
        return Tenant::for($this)->has($id);
    }

    public function assignPlugin(string $id): bool
    {
        if (! Tenant::hasDefinition($id)) {
            throw new InvalidArgumentException("Plugin [{$id}] is not registered.");
        }

        try {
            $this->pluginAssignments()->create([
                'plugin' => $id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        $this->unsetRelation('pluginAssignments');

        event(new PluginAssigned($this, $id));

        return true;
    }

    public function revokePlugin(string $id): bool
    {
        $deleted = $this->pluginAssignments()->where('plugin', $id)->delete();

        if ($deleted === 0) {
            return false;
        }

        $this->unsetRelation('pluginAssignments');

        event(new PluginRevoked($this, $id));

        return true;
    }

    /**
     * @return list<string>
     */
    public function pluginIds(): array
    {
        return $this->pluginAssignments()->pluck('plugin')->all();
    }
}
