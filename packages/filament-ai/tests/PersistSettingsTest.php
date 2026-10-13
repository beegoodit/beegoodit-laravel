<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\PersistSettings;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Enums\Lab;
use PHPUnit\Framework\Attributes\Test;

class PersistSettingsTest extends TestCase
{
    #[Test]
    public function it_persists_byok_settings_for_a_stub_workspace_resolver(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 42,
        ]);

        $registry = $this->app->make(CapabilityRegistry::class);
        $registry->register(new StubCapabilityProvider(
            id: 'cms',
            capabilities: [
                new CapabilityDefinition('publish', 'Publish', null, true, true, []),
            ],
        ));

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => Lab::OpenAI->value,
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test-secret',
            'capabilities' => [
                'cms' => ['publish' => false],
            ],
            'custom_instructions' => 'Be concise.',
        ]);

        $this->assertSame(42, $settings->workspaceKey());
        $this->assertTrue($settings->enabled);
        $this->assertSame(Lab::OpenAI, $settings->provider);
        $this->assertSame('gpt-4o-mini', $settings->model);
        $this->assertSame('sk-test-secret', $settings->api_key);
        $this->assertSame('Be concise.', $settings->custom_instructions);
        $this->assertFalse($settings->resolvedCapabilities()['cms']['publish']);
        $this->assertFalse($settings->can('cms', 'publish'));

        $raw = DB::table('beegoodit_filament_ai_settings')->where('workspace_id', 42)->first();
        $this->assertNotNull($raw);
        $this->assertNotSame('sk-test-secret', $raw->api_key);
        $this->assertStringNotContainsString('sk-test-secret', (string) $raw->api_key);
    }

    #[Test]
    public function it_keeps_existing_api_key_when_blank_on_save(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 7,
        ]);

        app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-keep-me',
        ]);

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => '',
        ]);

        $this->assertSame('sk-keep-me', $settings->api_key);
    }

    #[Test]
    public function access_service_requires_valid_byok(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 1,
        ]);

        $access = app(AccessService::class);
        $this->assertFalse($access->hasValidByok());

        app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-ok',
        ]);

        $this->assertTrue($access->hasValidByok());

        $user = $this->actingAsUser();
        $this->assertTrue($access->canUseAssistant($user));
        $this->assertTrue($access->canManageSettings($user));
    }

    #[Test]
    public function tool_context_binds_workspace_from_resolver(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 99,
        ]);

        $user = $this->actingAsUser();
        $context = app(ToolContext::class);
        $context->bind();

        $this->assertSame(99, $context->workspaceId());
        $this->assertSame($user->id, $context->user()?->getAuthIdentifier());
    }

    #[Test]
    public function for_current_workspace_uses_null_scope_without_resolver(): void
    {
        app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-single',
        ]);

        $settings = Setting::forCurrentWorkspace();

        $this->assertNotNull($settings);
        $this->assertNull($settings->workspaceKey());
        $this->assertSame('sk-single', $settings->api_key);
    }
}
