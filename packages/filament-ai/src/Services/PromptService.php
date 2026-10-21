<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Agents\ShellAgent;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Files\File;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StreamableAgentResponse;

class PromptService
{
    public function __construct(
        private PromptRateLimiter $rateLimiter,
        private AccessService $access,
    ) {}

    public function makeAgent(
        Authenticatable $user,
        ?string $conversationId = null,
        ?Setting $settings = null,
        ?ChatMode $mode = null,
    ): ShellAgent {
        $settings ??= FilamentAi::settingsModel()::forCurrentTenant();

        if ($settings === null || ! $settings->hasConfiguredKey()) {
            throw new \RuntimeException('AI assistant is not configured for this tenant.');
        }

        $context = ToolContext::fromRequest($conversationId, $mode);
        app()->instance(ToolContext::class, $context);

        return ShellAgent::make(
            context: $context,
            settings: $settings,
            user: $user,
        );
    }

    public function prepareAgent(
        ShellAgent $agent,
        Authenticatable $user,
        ?string $conversationId = null,
    ): ShellAgent {
        if ($conversationId !== null && $conversationId !== '') {
            return $agent->continue($conversationId, $user);
        }

        return $agent->forUser($user);
    }

    /**
     * @param  list<File>  $attachments
     */
    public function prompt(
        Authenticatable $user,
        string $message,
        ?string $conversationId = null,
        array $attachments = [],
        ?ChatMode $mode = null,
        ?string $model = null,
    ): AgentResponse {
        $this->guardPrompt($user);
        $settings = FilamentAi::settingsModel()::forCurrentTenant();
        $agent = $this->makeAgent($user, $conversationId, $settings, $mode);

        return $this->withProviderKey($settings, fn () => $this->prepareAgent($agent, $user, $conversationId)
            ->prompt(
                $message,
                $attachments,
                provider: $this->resolveProvider($settings),
                model: $this->resolveModel($settings, $model),
                timeout: (int) config('filament-ai.timeout', 120),
            ));
    }

    /**
     * @param  list<File>  $attachments
     */
    public function stream(
        Authenticatable $user,
        string $message,
        ?string $conversationId = null,
        array $attachments = [],
        ?ChatMode $mode = null,
        ?string $model = null,
    ): StreamableAgentResponse {
        $this->guardPrompt($user);
        $settings = FilamentAi::settingsModel()::forCurrentTenant();
        $agent = $this->makeAgent($user, $conversationId, $settings, $mode);

        return $this->withProviderKey($settings, fn () => $this->prepareAgent($agent, $user, $conversationId)
            ->stream(
                $message,
                $attachments,
                provider: $this->resolveProvider($settings),
                model: $this->resolveModel($settings, $model),
                timeout: (int) config('filament-ai.timeout', 120),
            ));
    }

    protected function resolveModel(?Setting $settings, ?string $model): ?string
    {
        $resolved = filled($model) ? trim($model) : null;

        if ($resolved !== null && $resolved !== '') {
            return $resolved;
        }

        $fromSettings = $settings?->model;

        return filled($fromSettings) ? (string) $fromSettings : null;
    }

    protected function guardPrompt(Authenticatable $user): void
    {
        if (! $this->access->canUseAssistant($user)) {
            abort(403, 'You are not allowed to use the AI assistant.');
        }

        $tenantId = FilamentAi::tenantId();
        $userId = $user->getAuthIdentifier();

        if ($this->rateLimiter->tooManyAttempts($tenantId, $userId)) {
            abort(429, 'Daily AI prompt limit reached for this tenant.');
        }

        $this->rateLimiter->hit($tenantId, $userId);
    }

    protected function resolveProvider(?Setting $settings): Lab|string
    {
        $provider = $settings?->provider ?? config('filament-ai.default_provider', 'openai');

        if ($provider instanceof Lab) {
            return $provider;
        }

        return Lab::tryFrom((string) $provider) ?? (string) $provider;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withProviderKey(?Setting $settings, callable $callback): mixed
    {
        if ($settings === null || ! $settings->hasConfiguredKey() || $settings->provider === null) {
            return $callback();
        }

        $provider = $settings->provider->value;
        $original = config("ai.providers.{$provider}.key");

        config(["ai.providers.{$provider}.key" => $settings->api_key]);

        try {
            return $callback();
        } finally {
            config(["ai.providers.{$provider}.key" => $original]);
        }
    }
}
