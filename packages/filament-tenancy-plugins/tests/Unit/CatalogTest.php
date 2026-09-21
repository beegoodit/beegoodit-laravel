<?php

declare(strict_types=1);

use BeegoodIT\FilamentTenancyPlugins\Tenant;

it('registers plugin definitions', function (): void {
    Tenant::register('billing', 'Billing', ['icon' => 'cash']);

    expect(Tenant::hasDefinition('billing'))->toBeTrue()
        ->and(Tenant::hasDefinition('unknown'))->toBeFalse();

    $definitions = Tenant::all();

    expect($definitions)->toHaveCount(1)
        ->and($definitions[0]->id)->toBe('billing')
        ->and($definitions[0]->label)->toBe('Billing')
        ->and($definitions[0]->meta)->toBe(['icon' => 'cash']);
});

it('no-ops when the same id and label are registered again even if meta differs', function (): void {
    Tenant::register('billing', 'Billing', ['icon' => 'cash']);
    Tenant::register('billing', 'Billing', ['icon' => 'credit-card']);

    expect(Tenant::all())->toHaveCount(1)
        ->and(Tenant::all()[0]->meta)->toBe(['icon' => 'cash']);
});

it('throws when the same id is registered with a different label', function (): void {
    Tenant::register('billing', 'Billing');

    Tenant::register('billing', 'Invoicing');
})->throws(InvalidArgumentException::class, 'Plugin [billing] is already registered as [Billing].');
