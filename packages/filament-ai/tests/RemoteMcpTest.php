<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Models\McpToken;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\McpAvailability;
use BeegoodIT\FilamentAi\Tests\Support\StubCapabilityProvider;
use BeegoodIT\FilamentAi\Tests\Support\StubReadTool;
use BeegoodIT\FilamentAi\Tests\Support\StubWriteTool;
use Laravel\Ai\Enums\Lab;
use Laravel\Mcp\Server\McpServiceProvider;
use PHPUnit\Framework\Attributes\Test;

final class RemoteMcpTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        $providers = parent::getPackageProviders($app);

        if (class_exists(McpServiceProvider::class)) {
            $providers[] = McpServiceProvider::class;
        }

        return $providers;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! McpAvailability::packagePresent()) {
            $this->markTestSkipped('laravel/mcp is not installed.');
        }

        app(CapabilityRegistry::class)->register(new StubCapabilityProvider(
            id: 'stub',
            capabilities: [
                new CapabilityDefinition('read', 'Read', null, true, true, [StubReadTool::class]),
                new CapabilityDefinition('write', 'Write', null, true, false, [StubWriteTool::class]),
            ],
        ));
    }

    private function enableMcp(): Setting
    {
        return Setting::query()->create([
            'workspace_id' => null,
            'provider' => Lab::OpenAI,
            'api_key' => 'sk-test',
            'model' => 'gpt-4o-mini',
            'enabled' => true,
            'mcp_enabled' => true,
            'capabilities' => app(CapabilityRegistry::class)->defaults(),
        ]);
    }

    /**
     * @return array{token: McpToken, plain: string}
     */
    private function issueToken(string $scope = McpToken::SCOPE_READ): array
    {
        $user = $this->actingAsUser();

        return McpToken::issue(
            label: 'test',
            workspaceId: null,
            createdByUserId: (int) $user->id,
            scope: $scope,
            activeFrom: now()->utc()->subDay(),
            activeTo: now()->utc()->addYear(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mcpPost(array $payload, ?string $plain = null)
    {
        $headers = [
            'Accept' => 'application/json, text/event-stream',
            'Content-Type' => 'application/json',
        ];

        if ($plain !== null) {
            $headers['Authorization'] = 'Bearer '.$plain;
        }

        return $this->postJson('/ai/mcp', $payload, $headers);
    }

    #[Test]
    public function mcp_rejects_missing_bearer_token(): void
    {
        $this->enableMcp();

        $this->mcpPost([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-03-26',
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'test', 'version' => '1.0'],
            ],
        ])->assertUnauthorized();
    }

    #[Test]
    public function mcp_disabled_returns_forbidden(): void
    {
        Setting::query()->create([
            'workspace_id' => null,
            'provider' => Lab::OpenAI,
            'api_key' => 'sk-test',
            'model' => 'gpt-4o-mini',
            'enabled' => true,
            'mcp_enabled' => false,
            'capabilities' => app(CapabilityRegistry::class)->defaults(),
        ]);

        $issued = $this->issueToken();

        $this->mcpPost([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-03-26',
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'test', 'version' => '1.0'],
            ],
        ], $issued['plain'])->assertForbidden();
    }

    #[Test]
    public function read_token_lists_ask_tools_only(): void
    {
        $this->enableMcp();
        $issued = $this->issueToken(McpToken::SCOPE_READ);

        $response = $this->mcpPost([
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => (object) [],
        ], $issued['plain']);

        $response->assertSuccessful();
        $names = $this->toolNamesFromResponse($response);

        $this->assertContains('stub-read-tool', $names);
        $this->assertNotContains('stub-write-tool', $names);
    }

    #[Test]
    public function write_token_includes_write_tools_when_capable(): void
    {
        $settings = $this->enableMcp();
        $settings->forceFill([
            'capabilities' => [
                'stub' => [
                    'read' => true,
                    'write' => true,
                ],
            ],
        ])->save();

        $issued = $this->issueToken(McpToken::SCOPE_WRITE);

        $response = $this->mcpPost([
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/list',
            'params' => (object) [],
        ], $issued['plain']);

        $response->assertSuccessful();
        $names = $this->toolNamesFromResponse($response);

        $this->assertContains('stub-read-tool', $names);
        $this->assertContains('stub-write-tool', $names);
    }

    /**
     * @return list<string>
     */
    private function toolNamesFromResponse($response): array
    {
        $json = $response->json();

        if (is_array($json) && isset($json['result']['tools'])) {
            return collect($json['result']['tools'])->pluck('name')->filter()->values()->all();
        }

        $content = (string) $response->getContent();
        $tools = [];

        foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
            $line = trim($line);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = json_decode(trim(substr($line, 5)), true);

            if (! is_array($payload)) {
                continue;
            }

            foreach ($payload['result']['tools'] ?? [] as $tool) {
                if (is_array($tool) && isset($tool['name'])) {
                    $tools[] = $tool['name'];
                }
            }
        }

        return $tools;
    }
}
