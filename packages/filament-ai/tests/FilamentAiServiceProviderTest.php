<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Tenant\NullTenantResolver;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use PHPUnit\Framework\Attributes\Test;

class FilamentAiServiceProviderTest extends TestCase
{
    #[Test]
    public function it_binds_a_capability_registry_with_the_shell_provider(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $this->assertInstanceOf(CapabilityRegistry::class, $registry);
        $this->assertCount(1, $registry->providers());
        $this->assertSame('filament_ai', $registry->providers()[0]->id());
    }

    #[Test]
    public function it_registers_capability_providers(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $registry->register(new StubCapabilityProvider(id: 'stub'));

        $this->assertCount(2, $registry->providers());
        $this->assertSame('stub', $registry->provider('stub')?->id());
    }

    #[Test]
    public function it_skips_unsupported_contract_versions(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);
        $before = count($registry->providers());

        $registry->register(new StubCapabilityProvider(
            id: 'old',
            contractVersion: 0,
        ));

        $this->assertCount($before, $registry->providers());
        $this->assertNull($registry->provider('old'));
    }

    #[Test]
    public function tenant_id_is_null_by_default(): void
    {
        $this->assertNull(FilamentAi::tenantId());
    }

    #[Test]
    public function null_tenant_resolver_returns_null(): void
    {
        config(['filament-ai.tenant_resolver' => NullTenantResolver::class]);

        $this->assertNull(FilamentAi::tenantId());
    }

    #[Test]
    public function tenant_helpers_read_config(): void
    {
        config([
            'filament-ai.tenant_column' => 'site_id',
            'filament-ai.tenant_key_type' => 'uuid',
        ]);

        $this->assertSame('site_id', FilamentAi::tenantColumn());
        $this->assertSame('uuid', FilamentAi::tenantKeyType());
    }
}
