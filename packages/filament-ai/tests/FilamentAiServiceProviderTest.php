<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use BeegoodIT\FilamentAi\Workspace\NullWorkspaceResolver;
use PHPUnit\Framework\Attributes\Test;

class FilamentAiServiceProviderTest extends TestCase
{
    #[Test]
    public function it_binds_an_empty_capability_registry(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $this->assertInstanceOf(CapabilityRegistry::class, $registry);
        $this->assertSame([], $registry->providers());
    }

    #[Test]
    public function it_registers_capability_providers(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $registry->register(new StubCapabilityProvider(id: 'stub'));

        $this->assertCount(1, $registry->providers());
        $this->assertSame('stub', $registry->provider('stub')?->id());
    }

    #[Test]
    public function it_skips_unsupported_contract_versions(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $registry->register(new StubCapabilityProvider(
            id: 'old',
            contractVersion: 0,
        ));

        $this->assertSame([], $registry->providers());
    }

    #[Test]
    public function workspace_id_is_null_by_default(): void
    {
        $this->assertNull(FilamentAi::workspaceId());
    }

    #[Test]
    public function null_workspace_resolver_returns_null(): void
    {
        config(['filament-ai.workspace_resolver' => NullWorkspaceResolver::class]);

        $this->assertNull(FilamentAi::workspaceId());
    }

    #[Test]
    public function workspace_helpers_read_config(): void
    {
        config([
            'filament-ai.workspace_column' => 'site_id',
            'filament-ai.workspace_key_type' => 'uuid',
        ]);

        $this->assertSame('site_id', FilamentAi::workspaceColumn());
        $this->assertSame('uuid', FilamentAi::workspaceKeyType());
    }
}
