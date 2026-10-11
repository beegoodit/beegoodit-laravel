<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console;

use BeegoodIT\FilamentTenancyPlugins\Console\Concerns\ResolvesConfiguredTenant;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Console\Command;

class RevokeCommand extends Command
{
    use ResolvesConfiguredTenant;

    protected $signature = 'tenancy-plugins:revoke {plugin : Catalog plugin id} {--tenant= : Tenant slug or key}';

    protected $description = 'Revoke a plugin assignment from a tenant';

    public function handle(): int
    {
        $plugin = $this->argument('plugin');

        if (! is_string($plugin) || $plugin === '') {
            $this->failCommand('A plugin id is required.');
        }

        if (! Tenant::hasDefinition($plugin)) {
            $this->failCommand("Unknown plugin [{$plugin}].");
        }

        $tenant = $this->resolveTenant();
        $label = $this->tenantLabel($tenant);

        if ($tenant->revokePlugin($plugin)) {
            $this->info("Plugin [{$plugin}] revoked from tenant [{$label}].");
        } else {
            $this->info("Plugin [{$plugin}] is not assigned to tenant [{$label}].");
        }

        return self::SUCCESS;
    }
}
