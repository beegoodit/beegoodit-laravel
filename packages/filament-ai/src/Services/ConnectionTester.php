<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Agents\ConnectionTestAgent;
use Laravel\Ai\AiManager;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Throwable;

class ConnectionTester
{
    public function __construct(
        private ProviderErrorPresenter $errors,
    ) {}

    public function test(?string $provider, ?string $apiKey, ?string $model = null): ConnectionTestResult
    {
        $providerValue = filled($provider) ? trim((string) $provider) : '';
        $key = filled($apiKey) ? trim((string) $apiKey) : '';
        $modelValue = filled($model) ? trim((string) $model) : null;

        if ($providerValue === '' || Lab::tryFrom($providerValue) === null) {
            return new ConnectionTestResult(
                successful: false,
                title: __('filament-ai::ai.pages.settings.test_connection_missing_provider'),
            );
        }

        if ($key === '') {
            return new ConnectionTestResult(
                successful: false,
                title: __('filament-ai::ai.pages.settings.test_connection_missing_key'),
            );
        }

        $lab = Lab::from($providerValue);
        $keyPath = "ai.providers.{$providerValue}.key";
        $originalKey = config($keyPath);

        config([$keyPath => $key]);
        app(AiManager::class)->forgetInstance($providerValue);

        try {
            (new ConnectionTestAgent)->prompt(
                'Reply with the single word OK.',
                provider: $lab,
                model: $modelValue,
                timeout: 30,
            );

            return new ConnectionTestResult(
                successful: true,
                title: __('filament-ai::ai.pages.settings.test_connection_success'),
                body: $modelValue !== null
                    ? __('filament-ai::ai.pages.settings.test_connection_success_body', ['model' => $modelValue])
                    : null,
            );
        } catch (ProviderConnectionException $exception) {
            return new ConnectionTestResult(
                successful: false,
                title: __('filament-ai::ai.pages.settings.test_connection_unreachable'),
                body: $this->sanitize($this->errors->present($exception), $key),
            );
        } catch (AiException|Throwable $exception) {
            return new ConnectionTestResult(
                successful: false,
                title: __('filament-ai::ai.pages.settings.test_connection_failed'),
                body: $this->sanitize($this->errors->present($exception), $key),
            );
        } finally {
            config([$keyPath => $originalKey]);
            app(AiManager::class)->forgetInstance($providerValue);
        }
    }

    private function sanitize(string $message, string $apiKey): string
    {
        if ($apiKey !== '' && str_contains($message, $apiKey)) {
            return str_replace($apiKey, '[redacted]', $message);
        }

        return $message;
    }
}
