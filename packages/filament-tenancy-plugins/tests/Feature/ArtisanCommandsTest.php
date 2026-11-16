<?php

declare(strict_types=1);

use BeegoodIT\FilamentTenancyPlugins\Models\PluginAssignment;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures\IncrementingTenant;

it('lists catalog and assignments without a tenant model', function (): void {
    config()->set('filament-tenancy-plugins.tenant_model', null);

    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    $this->artisan('tenancy-plugins:list')
        ->expectsOutputToContain('Registered plugins')
        ->expectsOutputToContain('Billing')
        ->expectsOutputToContain('UuidTenant')
        ->doesntExpectOutputToContain('acme')
        ->assertSuccessful();
});

it('lists tenant slugs when the tenant model is configured', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    $this->artisan('tenancy-plugins:list')
        ->expectsOutputToContain('acme')
        ->expectsOutputToContain('Billing')
        ->assertSuccessful();
});

it('assigns a plugin by tenant slug', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--tenant' => 'acme',
    ])
        ->expectsOutputToContain('Plugin [billing] assigned to tenant [acme].')
        ->assertSuccessful();

    expect($tenant->fresh()->hasPlugin('billing'))->toBeTrue();
});

it('assigns a plugin by tenant key on an incrementing model', function (): void {
    config()->set('filament-tenancy-plugins.tenant_model', IncrementingTenant::class);

    Tenant::register('billing', 'Billing');

    $tenant = $this->incrementingTenant();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--tenant' => (string) $tenant->getKey(),
    ])
        ->expectsOutputToContain('Plugin [billing] assigned to tenant [globex].')
        ->assertSuccessful();

    expect($tenant->fresh()->hasPlugin('billing'))->toBeTrue();
});

it('treats a second assign as a successful no-op', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--tenant' => 'acme',
    ])
        ->expectsOutputToContain('Plugin [billing] is already assigned to tenant [acme].')
        ->assertSuccessful();

    expect(PluginAssignment::query()->count())->toBe(1);
});

it('revokes a plugin from a tenant', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    $this->artisan('tenancy-plugins:revoke', [
        'plugin' => 'billing',
        '--tenant' => 'acme',
    ])
        ->expectsOutputToContain('Plugin [billing] revoked from tenant [acme].')
        ->assertSuccessful();

    expect($tenant->fresh()->hasPlugin('billing'))->toBeFalse();
});

it('treats revoke of a missing assignment as a successful no-op', function (): void {
    Tenant::register('billing', 'Billing');

    $this->uuidTenant();

    $this->artisan('tenancy-plugins:revoke', [
        'plugin' => 'billing',
        '--tenant' => 'acme',
    ])
        ->expectsOutputToContain('Plugin [billing] is not assigned to tenant [acme].')
        ->assertSuccessful();
});

it('fails when assigning an unknown plugin', function (): void {
    $this->uuidTenant();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'missing',
        '--tenant' => 'acme',
    ])
        ->expectsOutputToContain('Unknown plugin [missing].')
        ->assertFailed();
});

it('fails when assigning to an unknown tenant', function (): void {
    Tenant::register('billing', 'Billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--tenant' => 'orthos-fachlabor',
    ])
        ->expectsOutputToContain('Unknown tenant [orthos-fachlabor].')
        ->assertFailed();
});

it('fails when neither tenant nor user is provided', function (): void {
    Tenant::register('billing', 'Billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
    ])
        ->expectsOutputToContain('Provide exactly one of --tenant or --user.')
        ->assertFailed();
});

it('fails when both tenant and user are provided', function (): void {
    Tenant::register('billing', 'Billing');

    $this->uuidTenant();
    $this->user();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--tenant' => 'acme',
        '--user' => 'rva@beegoodit.de',
    ])
        ->expectsOutputToContain('Provide exactly one of --tenant or --user.')
        ->assertFailed();
});

it('assigns a plugin by user email', function (): void {
    Tenant::register('billing', 'Billing');

    $user = $this->user();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--user' => 'RVA@beegoodit.de',
    ])
        ->expectsOutputToContain('Plugin [billing] assigned to user [rva@beegoodit.de].')
        ->assertSuccessful();

    expect($user->fresh()->hasPlugin('billing'))->toBeTrue();
});

it('assigns a plugin by user key', function (): void {
    Tenant::register('billing', 'Billing');

    $user = $this->user();

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--user' => (string) $user->getKey(),
    ])
        ->expectsOutputToContain('Plugin [billing] assigned to user [rva@beegoodit.de].')
        ->assertSuccessful();

    expect($user->fresh()->hasPlugin('billing'))->toBeTrue();
});

it('treats a second user assign as a successful no-op', function (): void {
    Tenant::register('billing', 'Billing');

    $user = $this->user();
    $user->assignPlugin('billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--user' => 'rva@beegoodit.de',
    ])
        ->expectsOutputToContain('Plugin [billing] is already assigned to user [rva@beegoodit.de].')
        ->assertSuccessful();

    expect(PluginAssignment::query()->count())->toBe(1);
});

it('revokes a plugin from a user', function (): void {
    Tenant::register('billing', 'Billing');

    $user = $this->user();
    $user->assignPlugin('billing');

    $this->artisan('tenancy-plugins:revoke', [
        'plugin' => 'billing',
        '--user' => 'rva@beegoodit.de',
    ])
        ->expectsOutputToContain('Plugin [billing] revoked from user [rva@beegoodit.de].')
        ->assertSuccessful();

    expect($user->fresh()->hasPlugin('billing'))->toBeFalse();
});

it('fails when assigning to an unknown user', function (): void {
    Tenant::register('billing', 'Billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--user' => 'missing@example.test',
    ])
        ->expectsOutputToContain('Unknown user [missing@example.test].')
        ->assertFailed();
});

it('fails when user model is not configured', function (): void {
    config()->set('filament-tenancy-plugins.user_model', null);

    Tenant::register('billing', 'Billing');

    $this->artisan('tenancy-plugins:assign', [
        'plugin' => 'billing',
        '--user' => 'rva@beegoodit.de',
    ])
        ->expectsOutputToContain('Set filament-tenancy-plugins.user_model')
        ->assertFailed();
});

it('lists user emails when the user model is configured', function (): void {
    Tenant::register('billing', 'Billing');

    $user = $this->user();
    $user->assignPlugin('billing');

    $this->artisan('tenancy-plugins:list')
        ->expectsOutputToContain('rva@beegoodit.de')
        ->expectsOutputToContain('billing')
        ->assertSuccessful();
});
