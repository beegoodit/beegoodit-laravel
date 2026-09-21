<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console\Concerns;

use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Database\Eloquent\Model;

trait ResolvesConfiguredTenant
{
    protected function failCommand(string $message): never
    {
        $this->fail($message);
    }

    protected function resolveTenant(): Model
    {
        $identifier = $this->option('tenant');

        if (! is_string($identifier) || $identifier === '') {
            $this->failCommand('The --tenant option is required.');
        }

        $class = config('filament-tenancy-plugins.tenant_model');

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            $this->failCommand('Set filament-tenancy-plugins.tenant_model to assign or revoke plugins.');
        }

        $tenant = Tenant::find($identifier);

        if (! $tenant instanceof Model) {
            $this->failCommand("Unknown tenant [{$identifier}].");
        }

        if (! in_array(HasPlugins::class, class_uses_recursive($tenant), true)) {
            $this->failCommand('The tenant model must use '.HasPlugins::class.'.');
        }

        return $tenant;
    }

    protected function tenantLabel(Model $tenant): string
    {
        $slugAttribute = config('filament-tenancy-plugins.slug_attribute', 'slug');
        $slug = $tenant->getAttribute($slugAttribute);

        if (is_string($slug) && $slug !== '') {
            return $slug;
        }

        return (string) $tenant->getKey();
    }
}
