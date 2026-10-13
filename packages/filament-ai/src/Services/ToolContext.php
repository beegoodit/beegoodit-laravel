<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;

/**
 * Request-scoped context for AI tools (workspace + actor + mode).
 *
 * Soft-deps may read `siteId` (alias of workspaceId) after TallCMS pack-sync.
 */
class ToolContext
{
    public int|string|null $workspaceId = null;

    /** @var int|string|null Soft-dep alias of workspaceId */
    public int|string|null $siteId = null;

    public ?Authenticatable $user = null;

    public ?string $conversationId = null;

    public ChatMode $mode = ChatMode::Ask;

    public function __construct(
        int|string|null $workspaceId = null,
        ?Authenticatable $user = null,
        ?string $conversationId = null,
        ChatMode $mode = ChatMode::Ask,
    ) {
        $this->workspaceId = $workspaceId;
        $this->siteId = $workspaceId;
        $this->user = $user;
        $this->conversationId = $conversationId;
        $this->mode = $mode;
    }

    public static function fromRequest(?string $conversationId = null, ?ChatMode $mode = null): self
    {
        return new self(
            workspaceId: self::resolveWorkspaceId($conversationId),
            user: auth()->user(),
            conversationId: $conversationId,
            mode: $mode ?? self::resolveMode($conversationId),
        );
    }

    public function bind(int|string|null $workspaceId = null, ?Authenticatable $user = null, ?string $conversationId = null, ?ChatMode $mode = null): void
    {
        $resolved = $workspaceId ?? FilamentAi::workspaceId();
        $this->workspaceId = $resolved;
        $this->siteId = $resolved;
        $this->user = $user ?? auth()->user();
        $this->conversationId = $conversationId;
        if ($mode !== null) {
            $this->mode = $mode;
        }
    }

    public function clear(): void
    {
        $this->workspaceId = null;
        $this->siteId = null;
        $this->user = null;
        $this->conversationId = null;
        $this->mode = ChatMode::Ask;
    }

    public function workspaceId(): int|string|null
    {
        return $this->workspaceId;
    }

    public function user(): ?Authenticatable
    {
        return $this->user;
    }

    public function conversationId(): ?string
    {
        return $this->conversationId;
    }

    public function setConversationId(?string $conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    public static function resolveWorkspaceId(?string $conversationId): int|string|null
    {
        $column = FilamentAi::workspaceColumn();

        if ($conversationId !== null && $conversationId !== '' && self::conversationWorkspaceColumnAvailable($column)) {
            $conversation = Conversation::query()->find($conversationId);

            if ($conversation !== null) {
                $value = $conversation->getAttribute($column);

                if ($value !== null) {
                    return $value;
                }
            }
        }

        return FilamentAi::workspaceId();
    }

    public static function resolveMode(?string $conversationId): ChatMode
    {
        if ($conversationId !== null && $conversationId !== '') {
            $conversation = Conversation::query()->find($conversationId);

            if ($conversation !== null) {
                return $conversation->chatMode();
            }
        }

        return ChatMode::Agent;
    }

    protected static function conversationWorkspaceColumnAvailable(string $column): bool
    {
        $table = config('ai.conversations.tables.conversations', 'agent_conversations');

        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
