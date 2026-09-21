<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console;

use BeegoodIT\FilamentTenancyPlugins\Models\PluginAssignment;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ListCommand extends Command
{
    protected $signature = 'tenancy-plugins:list';

    protected $description = 'List registered tenant plugins and their assignments';

    public function handle(): int
    {
        $definitions = Tenant::all();

        $this->info('Registered plugins');

        if ($definitions === []) {
            $this->line('  (none)');
        } else {
            foreach ($definitions as $definition) {
                $this->line("  {$definition->id}  {$definition->label}");
            }
        }

        $assignments = PluginAssignment::query()
            ->orderBy('pluginable_type')
            ->orderBy('pluginable_id')
            ->orderBy('plugin')
            ->get();

        $this->newLine();
        $this->info('Assignments');

        if ($assignments->isEmpty()) {
            $this->line('  (none)');

            return self::SUCCESS;
        }

        foreach ($assignments as $assignment) {
            $slug = $this->slugFor($assignment);
            $suffix = $slug !== null ? "  {$slug}" : '';

            $this->line("  {$assignment->pluginable_type}  {$assignment->pluginable_id}  {$assignment->plugin}{$suffix}");
        }

        return self::SUCCESS;
    }

    private function slugFor(PluginAssignment $assignment): ?string
    {
        $class = config('filament-tenancy-plugins.tenant_model');

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            return null;
        }

        $tenant = new $class;

        if (! $tenant instanceof Model) {
            return null;
        }

        if ($assignment->pluginable_type !== $tenant->getMorphClass()) {
            return null;
        }

        try {
            $record = $class::query()->find($assignment->pluginable_id);
        } catch (Throwable) {
            return null;
        }

        if (! $record instanceof Model) {
            return null;
        }

        $slug = $record->getAttribute(config('filament-tenancy-plugins.slug_attribute', 'slug'));

        return is_string($slug) && $slug !== '' ? $slug : null;
    }
}
