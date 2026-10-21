<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Agents\ConnectionTestAgent;
use BeegoodIT\FilamentAi\Services\ConnectionTester;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Prompts\AgentPrompt;
use PHPUnit\Framework\Attributes\Test;

class ConnectionTesterTest extends TestCase
{
    #[Test]
    public function it_fails_when_provider_is_missing(): void
    {
        $result = app(ConnectionTester::class)->test(
            provider: null,
            apiKey: 'sk-test',
            model: 'gpt-4o-mini',
        );

        $this->assertFalse($result->successful);
        $this->assertSame(
            __('filament-ai::ai.pages.settings.test_connection_missing_provider'),
            $result->title,
        );
    }

    #[Test]
    public function it_fails_when_api_key_is_missing(): void
    {
        $result = app(ConnectionTester::class)->test(
            provider: Lab::OpenAI->value,
            apiKey: '',
            model: 'gpt-4o-mini',
        );

        $this->assertFalse($result->successful);
        $this->assertSame(
            __('filament-ai::ai.pages.settings.test_connection_missing_key'),
            $result->title,
        );
    }

    #[Test]
    public function it_succeeds_when_provider_accepts_the_probe(): void
    {
        ConnectionTestAgent::fake([
            'OK',
        ]);

        $result = app(ConnectionTester::class)->test(
            provider: Lab::OpenAI->value,
            apiKey: 'sk-test-secret',
            model: 'gpt-4o-mini',
        );

        $this->assertTrue($result->successful);
        $this->assertSame(
            __('filament-ai::ai.pages.settings.test_connection_success'),
            $result->title,
        );
        $this->assertSame(
            __('filament-ai::ai.pages.settings.test_connection_success_body', ['model' => 'gpt-4o-mini']),
            $result->body,
        );

        ConnectionTestAgent::assertPrompted(function (AgentPrompt $prompt): bool {
            return $prompt->contains('OK');
        });
    }

    #[Test]
    public function it_returns_failure_when_provider_rejects_the_probe(): void
    {
        ConnectionTestAgent::fake(function (): never {
            throw new AiException('Invalid API key: sk-test-secret');
        });

        $result = app(ConnectionTester::class)->test(
            provider: Lab::OpenAI->value,
            apiKey: 'sk-test-secret',
            model: 'gpt-4o-mini',
        );

        $this->assertFalse($result->successful);
        $this->assertSame(
            __('filament-ai::ai.pages.settings.test_connection_failed'),
            $result->title,
        );
        $this->assertSame('Invalid API key: [redacted]', $result->body);
    }
}
