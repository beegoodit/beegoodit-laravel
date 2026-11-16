<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console;

use BeegoodIT\FilamentTenancyPlugins\Console\Concerns\ResolvesConfiguredSubject;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Console\Command;

class AssignCommand extends Command
{
    use ResolvesConfiguredSubject;

    protected $signature = 'tenancy-plugins:assign {plugin : Catalog plugin id} {--tenant= : Tenant slug or key} {--user= : User email or key}';

    protected $description = 'Assign a registered plugin to a tenant or user';

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

        if ($subject->assignPlugin($plugin)) {
            $this->info("Plugin [{$plugin}] assigned to {$kind} [{$label}].");
        } else {
            $this->info("Plugin [{$plugin}] is already assigned to {$kind} [{$label}].");
        }

        return self::SUCCESS;
    }
}
