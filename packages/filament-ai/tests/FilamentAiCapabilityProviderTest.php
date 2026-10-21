<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\FilamentAiCapabilityProvider;
use BeegoodIT\FilamentAi\Services\PersistSettings;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use Laravel\Ai\Providers\Tools\WebFetch;
use Laravel\Ai\Providers\Tools\WebSearch;
use PHPUnit\Framework\Attributes\Test;

class FilamentAiCapabilityProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_shell_provider_first_on_boot(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $providers = $registry->providers();

        $this->assertNotSame([], $providers);
        $this->assertSame(FilamentAiCapabilityProvider::Id, $providers[0]->id());
        $this->assertInstanceOf(FilamentAiCapabilityProvider::class, $registry->provider(FilamentAiCapabilityProvider::Id));
    }

    #[Test]
    public function web_access_defaults_to_off(): void
    {
        $defaults = $this->app->make(CapabilityRegistry::class)->defaults();

        $this->assertFalse($defaults[FilamentAiCapabilityProvider::Id][FilamentAiCapabilityProvider::WebAccess] ?? true);
    }

    #[Test]
    public function ask_tools_exclude_web_natives_when_web_access_is_off(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test',
            'capabilities' => [
                FilamentAiCapabilityProvider::Id => [
                    FilamentAiCapabilityProvider::WebAccess => false,
                ],
            ],
        ]);

        $tools = $registry->toolsFor($settings, ChatMode::Ask);
        $classes = array_map(static fn (object $tool): string => $tool::class, $tools);

        $this->assertNotContains(WebSearch::class, $classes);
        $this->assertNotContains(WebFetch::class, $classes);
    }

    #[Test]
    public function ask_tools_include_web_natives_when_web_access_is_on(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test',
            'capabilities' => [
                FilamentAiCapabilityProvider::Id => [
                    FilamentAiCapabilityProvider::WebAccess => true,
                ],
            ],
        ]);

        $tools = $registry->toolsFor($settings, ChatMode::Ask);
        $classes = array_map(static fn (object $tool): string => $tool::class, $tools);

        $this->assertContains(WebSearch::class, $classes);
        $this->assertContains(WebFetch::class, $classes);
    }

    #[Test]
    public function shell_provider_stays_first_when_host_providers_register_later(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);
        $registry->register(new StubCapabilityProvider(id: 'crm', label: 'CRM'));

        $ids = array_map(
            static fn ($provider): string => $provider->id(),
            $registry->providers(),
        );

        $this->assertSame(FilamentAiCapabilityProvider::Id, $ids[0]);
        $this->assertContains('crm', $ids);
    }
}
