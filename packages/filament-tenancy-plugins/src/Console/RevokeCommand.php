<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console;

use BeegoodIT\FilamentTenancyPlugins\Console\Concerns\ResolvesConfiguredSubject;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Console\Command;

class RevokeCommand extends Command
{
    use ResolvesConfiguredSubject;

    protected $signature = 'tenancy-plugins:revoke {plugin : Catalog plugin id} {--tenant= : Tenant slug or key} {--user= : User email or key}';

    protected $description = 'Revoke a plugin assignment from a tenant or user';

    public function handle(): int
    {
        $plugin = $this->argument('plugin');

        if (! is_string($plugin) || $plugin === '') {
            $this->failCommand('A plugin id is required.');
        }

        if (! Tenant::hasDefinition($plugin)) {
            $this->failCommand("Unknown plugin [{$plugin}].");
        }

        [$subject, $kind] = $this->resolveSubject();
        $label = $this->subjectLabel($subject, $kind);

        if ($subject->revokePlugin($plugin)) {
            $this->info("Plugin [{$plugin}] revoked from {$kind} [{$label}].");
        } else {
            $this->info("Plugin [{$plugin}] is not assigned to {$kind} [{$label}].");
        }

        return self::SUCCESS;
    }
}
