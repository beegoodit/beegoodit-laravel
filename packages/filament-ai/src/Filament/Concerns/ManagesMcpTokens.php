<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Concerns;

use BeegoodIT\FilamentAi\Models\McpToken;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Support\McpAvailability;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

trait ManagesMcpTokens
{
    public string $newMcpTokenLabel = '';

    public ?string $plainMcpToken = null;

    /**
     * @return Collection<int, McpToken>
     */
    public function getMcpTokensProperty(): Collection
    {
        if (! McpAvailability::packagePresent()) {
            return collect();
        }

        return McpToken::query()
            ->forWorkspace(FilamentAi::workspaceId())
            ->latest('id')
            ->with('createdBy')
            ->get();
    }

    public function mcpConnectionSnippet(): string
    {
        $path = (string) config('filament-ai.mcp.path', '/ai/mcp');
        $url = url($path);
        $prefix = (string) config('filament-ai.mcp.token_prefix', 'fa_mcp_');
        $serverKey = (string) config('filament-ai.mcp.server_name', 'filament-ai');

        return <<<JSON
{
  "mcpServers": {
    "{$serverKey}": {
      "url": "{$url}",
      "headers": {
        "Authorization": "Bearer {$prefix}PASTE_TOKEN_HERE"
      }
    }
  }
}
JSON;
    }

    public function createMcpToken(): void
    {
        $this->issueMcpToken(McpToken::SCOPE_READ);
    }

    public function createMcpWriteToken(): void
    {
        $this->issueMcpToken(McpToken::SCOPE_WRITE);
    }

    protected function issueMcpToken(string $scope): void
    {
        if (! McpAvailability::packagePresent()) {
            return;
        }

        $label = trim($this->newMcpTokenLabel);

        if ($label === '') {
            Notification::make()
                ->title(__('filament-ai::ai.mcp.token_label_required'))
                ->danger()
                ->send();

            return;
        }

        $user = auth()->user();

        if ($user === null) {
            Notification::make()
                ->title(__('filament-ai::ai.mcp.token_auth_required'))
                ->danger()
                ->send();

            return;
        }

        $issued = McpToken::issue(
            label: $label,
            workspaceId: FilamentAi::workspaceId(),
            createdByUserId: (int) $user->getAuthIdentifier(),
            scope: $scope,
        );

        $this->plainMcpToken = $issued['plain'];
        $this->newMcpTokenLabel = '';

        Notification::make()
            ->title(__('filament-ai::ai.mcp.token_created_title'))
            ->success()
            ->send();
    }

    public function revokeMcpToken(int $tokenId): void
    {
        if (! McpAvailability::packagePresent()) {
            return;
        }

        $token = McpToken::query()
            ->forWorkspace(FilamentAi::workspaceId())
            ->whereKey($tokenId)
            ->first();

        if ($token === null) {
            return;
        }

        $token->revoke();

        Notification::make()
            ->title(__('filament-ai::ai.mcp.token_revoked_notice'))
            ->success()
            ->send();
    }
}
