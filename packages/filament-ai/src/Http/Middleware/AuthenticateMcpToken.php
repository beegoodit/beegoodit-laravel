<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Http\Middleware;

use BeegoodIT\FilamentAi\Models\McpToken;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Support\SubjectScope;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokensTable = (string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens');
        $settingsTable = FilamentAi::settingsTable();

        if (! Schema::hasTable($tokensTable) || ! Schema::hasTable($settingsTable)) {
            return response()->json(['message' => 'MCP is not available.'], 503);
        }

        $plain = $this->bearerToken($request);

        if ($plain === null) {
            return response()->json(['message' => 'Missing Bearer token.'], 401);
        }

        $token = McpToken::findByPlainToken($plain);

        if ($token === null || ! $token->isCurrentlyValid()) {
            return response()->json(['message' => 'Invalid or expired MCP token.'], 401);
        }

        $user = $this->resolveCreatorUser($token);

        if ($user === null) {
            return response()->json(['message' => 'MCP token has no valid creator user.'], 401);
        }

        $settings = SubjectScope::apply(FilamentAi::settings(), self::subjectFromToken($token))->first();

        if ($settings === null || ! $settings->enabled || ! $settings->mcp_enabled) {
            return response()->json(['message' => 'Remote MCP is disabled for this tenant.'], 403);
        }

        Auth::setUser($user);

        $mode = $token->allowsWrite() ? ChatMode::Agent : ChatMode::Ask;

        $subject = self::subjectFromToken($token);

        config([
            'filament-ai.tenant_resolver' => static fn (): ?\Illuminate\Database\Eloquent\Model => $subject,
        ]);

        app()->instance('filament_ai.mcp.settings', $settings);
        app()->instance(ToolContext::class, new ToolContext(
            tenantId: $subject?->getKey(),
            user: $user,
            conversationId: null,
            mode: $mode,
        ));
        app()->instance(McpToken::class, $token);
        $request->attributes->set('filament_ai_mcp_token', $token);
        $request->attributes->set('filament_ai_mcp_settings', $settings);

        $hook = config('filament-ai.mcp.after_authenticate');

        if (is_callable($hook)) {
            $hook($request, $token, $settings, $user);
        }

        $token->touchLastUsed();

        return $next($request);
    }

    private static function subjectFromToken(McpToken $token): ?Model
    {
        $type = $token->getAttribute('subject_type');
        $id = $token->subjectKey();

        if (! is_string($type) || $type === '' || $id === null || ! is_a($type, Model::class, true)) {
            return null;
        }

        $subject = new $type;
        $subject->setAttribute($subject->getKeyName(), $id);
        $subject->exists = true;

        return $subject;
    }

    private function resolveCreatorUser(McpToken $token): ?Authenticatable
    {
        if ($token->created_by_user_id === null) {
            return null;
        }

        $model = (string) config('auth.providers.users.model');

        if ($model === '' || ! class_exists($model)) {
            return null;
        }

        $user = $model::query()->find($token->created_by_user_id);

        if (! $user instanceof Authenticatable) {
            return null;
        }

        if (isset($user->is_active) && $user->is_active === false) {
            return null;
        }

        return $user;
    }

    private function bearerToken(Request $request): ?string
    {
        $plain = $request->bearerToken();

        if (! is_string($plain)) {
            return null;
        }

        $plain = trim($plain);

        return $plain !== '' ? $plain : null;
    }
}
