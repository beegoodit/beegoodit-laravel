<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins\Console\Concerns;

use BeegoodIT\FilamentTenancyPlugins\Models\Concerns\HasPlugins;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use Illuminate\Database\Eloquent\Model;

trait ResolvesConfiguredSubject
{
    protected function failCommand(string $message): never
    {
        $this->fail($message);
    }

    /**
     * @return array{0: Model, 1: 'tenant'|'user'}
     */
    protected function resolveSubject(): array
    {
        $tenantOption = $this->option('tenant');
        $userOption = $this->option('user');

        $hasTenant = is_string($tenantOption) && $tenantOption !== '';
        $hasUser = is_string($userOption) && $userOption !== '';

        if ($hasTenant && $hasUser) {
            $this->failCommand('Provide exactly one of --tenant or --user.');
        }

        if (! $hasTenant && ! $hasUser) {
            $this->failCommand('Provide exactly one of --tenant or --user.');
        }

        if ($hasUser) {
            return [$this->resolveUser($userOption), 'user'];
        }

        return [$this->resolveTenant($tenantOption), 'tenant'];
    }

    protected function resolveTenant(string $identifier): Model
    {
        $class = config('filament-tenancy-plugins.tenant_model');

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            $this->failCommand('Set filament-tenancy-plugins.tenant_model to assign or revoke plugins with --tenant.');
        }

        $tenant = Tenant::find($identifier);

        if (! $tenant instanceof Model) {
            $this->failCommand("Unknown tenant [{$identifier}].");
        }

        $this->ensureHasPlugins($tenant);

        return $tenant;
    }

    protected function resolveUser(string $identifier): Model
    {
        $class = config('filament-tenancy-plugins.user_model');

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            $this->failCommand('Set filament-tenancy-plugins.user_model to assign or revoke plugins with --user.');
        }

        $user = Tenant::findUser($identifier);

        if (! $user instanceof Model) {
            $this->failCommand("Unknown user [{$identifier}].");
        }

        $this->ensureHasPlugins($user);

        return $user;
    }

    protected function ensureHasPlugins(Model $subject): void
    {
        if (! in_array(HasPlugins::class, class_uses_recursive($subject), true)) {
            $this->failCommand('The subject model must use '.HasPlugins::class.'.');
        }
    }

    /**
     * @param  'tenant'|'user'  $kind
     */
    protected function subjectLabel(Model $subject, string $kind): string
    {
        if ($kind === 'user') {
            $lookupAttribute = config('filament-tenancy-plugins.user_lookup_attribute', 'email');
            $email = $subject->getAttribute($lookupAttribute);

            if (is_string($email) && $email !== '') {
                return $email;
            }

            return (string) $subject->getKey();
        }

        $slugAttribute = config('filament-tenancy-plugins.slug_attribute', 'slug');
        $slug = $subject->getAttribute($slugAttribute);

        if (is_string($slug) && $slug !== '') {
            return $slug;
        }

        return (string) $subject->getKey();
    }
}
