<?php

declare(strict_types=1);

use BeegoodIT\FilamentTenancyPlugins\Events\PluginAssigned;
use BeegoodIT\FilamentTenancyPlugins\Events\PluginRevoked;
use BeegoodIT\FilamentTenancyPlugins\Exceptions\NotAssignedException;
use BeegoodIT\FilamentTenancyPlugins\Models\PluginAssignment;
use BeegoodIT\FilamentTenancyPlugins\Tenant;
use BeegoodIT\FilamentTenancyPlugins\Tests\Fixtures\IncrementingTenant;
use Filament\Facades\Filament;
use Filament\FilamentManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

it('uses the hardcoded assignments table', function (): void {
    expect(Schema::hasTable('beegoodit_filament_tenancy_plugins_assignments'))->toBeTrue()
        ->and((new PluginAssignment)->getTable())->toBe('beegoodit_filament_tenancy_plugins_assignments');
});

it('assigns and revokes plugins on uuid and incrementing tenants', function (string $factory): void {
    Tenant::register('billing', 'Billing');

    Event::fake([PluginAssigned::class, PluginRevoked::class]);

    $tenant = $this->{$factory}();

    expect($tenant->assignPlugin('billing'))->toBeTrue();

    expect($tenant->hasPlugin('billing'))->toBeTrue()
        ->and(Tenant::for($tenant)->has('billing'))->toBeTrue()
        ->and(Tenant::allows($tenant, 'billing'))->toBeTrue()
        ->and(Tenant::allows($tenant, 'missing'))->toBeFalse()
        ->and($tenant->pluginIds())->toBe(['billing']);

    Event::assertDispatched(PluginAssigned::class, function (PluginAssigned $event) use ($tenant): bool {
        return $event->tenant->is($tenant) && $event->plugin === 'billing';
    });

    expect($tenant->assignPlugin('billing'))->toBeFalse();

    expect(PluginAssignment::query()->count())->toBe(1);
    Event::assertDispatched(PluginAssigned::class, 1);

    expect($tenant->revokePlugin('billing'))->toBeTrue();

    expect($tenant->hasPlugin('billing'))->toBeFalse()
        ->and(Tenant::for($tenant)->has('billing'))->toBeFalse()
        ->and($tenant->pluginIds())->toBe([]);

    Event::assertDispatched(PluginRevoked::class, function (PluginRevoked $event) use ($tenant): bool {
        return $event->tenant->is($tenant) && $event->plugin === 'billing';
    });

    expect($tenant->revokePlugin('billing'))->toBeFalse();

    Event::assertDispatched(PluginRevoked::class, 1);
})->with([
    'uuid tenant' => 'uuidTenant',
    'incrementing tenant' => 'incrementingTenant',
]);

it('rejects assigning an unknown catalog id', function (): void {
    $tenant = $this->uuidTenant();

    $tenant->assignPlugin('missing');
})->throws(InvalidArgumentException::class, 'Plugin [missing] is not registered.');

it('treats a unique assignment conflict as an idempotent no-op', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();

    PluginAssignment::query()->create([
        'pluginable_type' => $tenant->getMorphClass(),
        'pluginable_id' => (string) $tenant->getKey(),
        'plugin' => 'billing',
    ]);

    Event::fake([PluginAssigned::class]);

    expect($tenant->assignPlugin('billing'))->toBeFalse();

    expect(PluginAssignment::query()->count())->toBe(1);
    Event::assertNotDispatched(PluginAssigned::class);
});

it('enforces a unique morph and plugin assignment', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();

    PluginAssignment::query()->create([
        'pluginable_type' => $tenant->getMorphClass(),
        'pluginable_id' => (string) $tenant->getKey(),
        'plugin' => 'billing',
    ]);

    expect(fn () => PluginAssignment::query()->create([
        'pluginable_type' => $tenant->getMorphClass(),
        'pluginable_id' => (string) $tenant->getKey(),
        'plugin' => 'billing',
    ]))->toThrow(QueryException::class);
});

it('returns false for Tenant::allows when the tenant is null', function (): void {
    Tenant::register('billing', 'Billing');

    expect(Tenant::allows(null, 'billing'))->toBeFalse();
});

it('returns false for Tenant::allows when the tenant is unassigned', function (string $factory): void {
    Tenant::register('billing', 'Billing');

    expect(Tenant::allows($this->{$factory}(), 'billing'))->toBeFalse();
})->with([
    'uuid tenant' => 'uuidTenant',
    'incrementing tenant' => 'incrementingTenant',
]);

it('returns false for Tenant::has when no Filament tenant is bound', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    expect(Tenant::has('billing'))->toBeFalse();
});

it('returns true for Tenant::has when a Filament tenant is bound', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    $filament = Mockery::mock(FilamentManager::class);
    $filament->shouldReceive('getTenant')->twice()->andReturn($tenant);
    Filament::swap($filament);

    expect(Tenant::has('billing'))->toBeTrue()
        ->and(Tenant::has('missing'))->toBeFalse();
});

it('finds a tenant by slug or key', function (): void {
    $tenant = $this->uuidTenant();

    expect(Tenant::find('acme')?->is($tenant))->toBeTrue()
        ->and(Tenant::find((string) $tenant->getKey())?->is($tenant))->toBeTrue()
        ->and(Tenant::find('missing'))->toBeNull()
        ->and(Tenant::find('orthos-fachlabor'))->toBeNull();
});

it('finds an incrementing tenant by slug or key without treating a slug as a key', function (): void {
    config()->set('filament-tenancy-plugins.tenant_model', IncrementingTenant::class);

    $tenant = $this->incrementingTenant();

    expect(Tenant::find('globex')?->is($tenant))->toBeTrue()
        ->and(Tenant::find((string) $tenant->getKey())?->is($tenant))->toBeTrue()
        ->and(Tenant::find('orthos-fachlabor'))->toBeNull();
});

it('returns null from Tenant::find when the tenant model is not configured', function (): void {
    config()->set('filament-tenancy-plugins.tenant_model', null);

    $this->uuidTenant();

    expect(Tenant::find('acme'))->toBeNull();
});

it('does not throw when Tenant::ensure is assigned', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();
    $tenant->assignPlugin('billing');

    expect(fn () => Tenant::ensure($tenant, 'billing'))->not->toThrow(NotAssignedException::class)
        ->and(fn () => Tenant::ensure('acme', 'billing'))->not->toThrow(NotAssignedException::class);
});

it('throws when Tenant::ensure is unassigned or unknown', function (): void {
    Tenant::register('billing', 'Billing');

    $tenant = $this->uuidTenant();

    expect(fn () => Tenant::ensure($tenant, 'billing'))
        ->toThrow(NotAssignedException::class, 'Plugin [billing] is not assigned.')
        ->and(fn () => Tenant::ensure('missing', 'billing'))
        ->toThrow(NotAssignedException::class, 'Plugin [billing] is not assigned.');
});
