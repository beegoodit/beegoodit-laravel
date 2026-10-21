<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\Exceptions\DuplicateToolNameException;
use BeegoodIT\FilamentAi\Tests\Support\FakeToolAlpha as SupportFakeToolAlpha;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use PHPUnit\Framework\Attributes\Test;

class CapabilityRegistryTest extends TestCase
{
    #[Test]
    public function it_builds_nested_defaults(): void
    {
        $registry = new CapabilityRegistry;
        $registry->register($this->cmsProvider());
        $registry->register($this->menuProvider());

        $this->assertSame([
            'cms' => [
                'publish' => true,
                'draft' => false,
            ],
            'menu' => [
                'manage' => true,
            ],
        ], $registry->defaults());
    }

    #[Test]
    public function it_merges_stored_capabilities_over_defaults(): void
    {
        $registry = new CapabilityRegistry;
        $registry->register($this->cmsProvider());

        $merged = $registry->merge([
            'cms' => [
                'publish' => false,
                'draft' => true,
            ],
            'legacy' => [
                'old' => true,
            ],
        ]);

        $this->assertFalse($merged['cms']['publish']);
        $this->assertTrue($merged['cms']['draft']);
        $this->assertTrue($merged['legacy']['old']);
    }

    #[Test]
    public function it_nests_flat_maps_when_a_single_provider_is_registered(): void
    {
        $registry = new CapabilityRegistry;
        $registry->register($this->cmsProvider());

        $merged = $registry->merge([
            'publish' => false,
            'draft' => true,
        ]);

        $this->assertFalse($merged['cms']['publish']);
        $this->assertTrue($merged['cms']['draft']);
    }

    #[Test]
    public function can_reads_nested_resolved_map(): void
    {
        $registry = new CapabilityRegistry;
        $resolved = [
            'cms' => ['publish' => true],
        ];

        $this->assertTrue($registry->can($resolved, 'cms', 'publish'));
        $this->assertFalse($registry->can($resolved, 'cms', 'missing'));
        $this->assertFalse($registry->can($resolved, 'other', 'publish'));
    }

    #[Test]
    public function tools_for_skips_capabilities_when_requires_are_unmet(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);
        $registry->register(new StubCapabilityProvider(
            id: 'cms',
            label: 'CMS',
            capabilities: [
                new CapabilityDefinition('media_write', 'Media write', null, false, false, []),
                new CapabilityDefinition(
                    'generate_media',
                    'Generate',
                    null,
                    false,
                    false,
                    [SupportFakeToolAlpha::class],
                    requires: ['media_write'],
                ),
            ],
        ));

        $settings = new class extends \BeegoodIT\FilamentAi\Models\Setting
        {
            public function resolvedCapabilities(): array
            {
                return [
                    'cms' => [
                        'media_write' => false,
                        'generate_media' => true,
                    ],
                ];
            }
        };

        $tools = $registry->toolsFor($settings, 'agent');

        $this->assertSame([], $tools);
    }

    #[Test]
    public function it_rejects_duplicate_tool_basenames_across_providers(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $registry->register(new StubCapabilityProvider(
            id: 'a',
            alwaysOnTools: [SupportFakeToolAlpha::class],
        ));

        $this->expectException(DuplicateToolNameException::class);

        $registry->register(new StubCapabilityProvider(
            id: 'b',
            alwaysOnTools: [Fixtures\FakeToolAlpha::class],
        ));
    }

    private function cmsProvider(): StubCapabilityProvider
    {
        return new StubCapabilityProvider(
            id: 'cms',
            label: 'CMS',
            capabilities: [
                new CapabilityDefinition('publish', 'Publish', null, true, true, []),
                new CapabilityDefinition('draft', 'Draft', null, false, true, []),
            ],
        );
    }

    private function menuProvider(): StubCapabilityProvider
    {
        return new StubCapabilityProvider(
            id: 'menu',
            label: 'Menu',
            capabilities: [
                new CapabilityDefinition('manage', 'Manage', null, true, false, []),
            ],
        );
    }
}
