<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Mcp;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\McpToken;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Laravel\Ai\Contracts\Tool as AiTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Filament AI')]
#[Version('1.0.0')]
#[Instructions('Workspace tools bound to your MCP token. Read tokens expose Ask-equivalent tools only. Write tokens expose Agent-equivalent tools gated by AI Settings capabilities.')]
class FilamentAiMcpServer extends Server
{
    protected string $name = 'Filament AI';

    protected string $version = '1.0.0';

    public int $defaultPaginationLength = 100;

    public int $maxPaginationLength = 200;

    protected function boot(): void
    {
        $this->name = (string) config('filament-ai.mcp.server_name', $this->name);
        $this->tools = $this->resolveTools();
    }

    /**
     * @return list<\Laravel\Mcp\Server\Tool>
     */
    private function resolveTools(): array
    {
        /** @var Setting|null $settings */
        $settings = app()->bound('filament_ai.mcp.settings')
            ? app('filament_ai.mcp.settings')
            : Setting::forCurrentWorkspace();

        if (! $settings instanceof Setting) {
            return [];
        }

        $token = app()->bound(McpToken::class) ? app(McpToken::class) : null;
        $mode = ($token instanceof McpToken && $token->allowsWrite())
            ? ChatMode::Agent
            : ChatMode::Ask;

        $registry = app(CapabilityRegistry::class);
        $askToolClasses = [];

        foreach ($registry->toolsFor($settings, ChatMode::Ask) as $askTool) {
            if ($askTool instanceof AiTool) {
                $askToolClasses[$askTool::class] = true;
            }
        }

        $tools = [];

        foreach ($registry->toolsFor($settings, $mode) as $tool) {
            if (! $tool instanceof AiTool) {
                continue;
            }

            $readOnly = $mode === ChatMode::Ask
                || isset($askToolClasses[$tool::class]);

            $tools[] = new ToolMcpAdapter($tool, $readOnly);
        }

        return $tools;
    }
}
