<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Models;

use BeegoodIT\FilamentAi\Services\ConversationAttachmentCleanup;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Laravel\Ai\Models\Conversation as AgentConversation;

class Conversation extends AgentConversation
{
    protected static function booted(): void
    {
        static::deleting(function (Conversation $conversation): void {
            app(ConversationAttachmentCleanup::class)->deleteForConversation($conversation);
            $conversation->messages()->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => ChatMode::class,
        ];
    }

    public function chatMode(): ChatMode
    {
        $mode = $this->getAttribute('mode');

        if ($mode instanceof ChatMode) {
            return $mode;
        }

        return ChatMode::tryFromConfig(is_string($mode) ? $mode : null);
    }

    public function workspaceKey(): int|string|null
    {
        return $this->getAttribute(FilamentAi::workspaceColumn());
    }

    public function setWorkspaceKey(int|string|null $value): static
    {
        $this->setAttribute(FilamentAi::workspaceColumn(), $value);

        return $this;
    }
}
