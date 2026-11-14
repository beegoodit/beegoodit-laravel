<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Services\PersistSettings;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use BeegoodIT\FilamentAi\Tests\Support\StubReadTool;
use BeegoodIT\FilamentAi\Tests\Support\StubWriteTool;
use PHPUnit\Framework\Attributes\Test;

class ChatModeAndToolsTest extends TestCase
{
    #[Test]
    public function ask_and_plan_are_read_only(): void
    {
        $this->assertTrue(ChatMode::Ask->isReadOnly());
        $this->assertTrue(ChatMode::Plan->isReadOnly());
        $this->assertFalse(ChatMode::Agent->isReadOnly());
        $this->assertTrue(ChatMode::Agent->allowsWrites());
    }

    #[Test]
    public function tools_for_respects_mode_and_capabilities(): void
    {
        $registry = $this->app->make(CapabilityRegistry::class);
        $registry->register(new StubCapabilityProvider(
            id: 'cms',
            capabilities: [
                new CapabilityDefinition('read', 'Read', null, true, true, [StubReadTool::class]),
                new CapabilityDefinition('write', 'Write', null, true, false, [StubWriteTool::class]),
            ],
        ));

        $settings = app(PersistSettings::class)->save([
            'enabled' => true,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_key' => 'sk-test',
            'capabilities' => [
                'cms' => ['read' => true, 'write' => true],
            ],
        ]);

        $askTools = $registry->toolsFor($settings, ChatMode::Ask);
        $agentTools = $registry->toolsFor($settings, ChatMode::Agent);

        $this->assertCount(1, $askTools);
        $this->assertInstanceOf(StubReadTool::class, $askTools[0]);
        $this->assertCount(2, $agentTools);
    }

    #[Test]
    public function tools_for_is_empty_when_only_default_off_shell_capabilities_exist(): void
    {
        $settings = new Setting([
            'enabled' => true,
            'capabilities' => [],
        ]);

        $tools = $this->app->make(CapabilityRegistry::class)->toolsFor($settings, ChatMode::Agent);

        $this->assertSame([], $tools);
    }
}
