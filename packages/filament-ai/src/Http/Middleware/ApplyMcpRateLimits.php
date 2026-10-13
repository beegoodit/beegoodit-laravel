<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Http\Middleware;

use BeegoodIT\FilamentAi\Models\McpToken;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyMcpRateLimits
{
    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var McpToken|null $token */
        $token = $request->attributes->get('filament_ai_mcp_token');

        $perToken = (int) config('filament-ai.mcp.rate_limit.per_token_per_minute', 120);
        $perWorkspace = (int) config('filament-ai.mcp.rate_limit.per_workspace_per_minute', 600);
        $perCall = (int) config('filament-ai.mcp.rate_limit.tools_call_per_minute', 60);

        $tokenKey = $token !== null ? 'token:'.$token->id : 'ip:'.$request->ip();
        $workspaceKey = $token !== null
            ? 'workspace:'.($token->workspaceKey() ?? 'none')
            : 'workspace:unknown';

        if ($this->tooManyAttempts('filament-ai-mcp', $tokenKey, $perToken)
            || $this->tooManyAttempts('filament-ai-mcp-workspace', $workspaceKey, $perWorkspace)) {
            return $this->tooManyResponse();
        }

        if ($this->isToolsCall($request)
            && $this->tooManyAttempts('filament-ai-mcp-call', $tokenKey, $perCall)) {
            return $this->tooManyResponse();
        }

        $this->limiter->hit('filament-ai-mcp:'.$tokenKey, 60);
        $this->limiter->hit('filament-ai-mcp-workspace:'.$workspaceKey, 60);

        if ($this->isToolsCall($request)) {
            $this->limiter->hit('filament-ai-mcp-call:'.$tokenKey, 60);
        }

        return $next($request);
    }

    private function tooManyAttempts(string $name, string $key, int $max): bool
    {
        return $this->limiter->tooManyAttempts($name.':'.$key, $max);
    }

    private function isToolsCall(Request $request): bool
    {
        $payload = $request->json()->all();

        return is_array($payload) && ($payload['method'] ?? null) === 'tools/call';
    }

    private function tooManyResponse(): Response
    {
        return response()->json(['message' => 'MCP rate limit exceeded.'], 429);
    }
}
