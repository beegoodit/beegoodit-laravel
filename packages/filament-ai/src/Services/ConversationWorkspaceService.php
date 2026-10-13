<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Support\Facades\Schema;

class ConversationWorkspaceService
{
    public function ensureWorkspaceScope(string $conversationId, object $participant): void
    {
        $column = FilamentAi::workspaceColumn();

        if (! $this->columnAvailable($column)) {
            return;
        }

        $workspaceId = FilamentAi::workspaceId();

        if ($workspaceId === null) {
            return;
        }

        Conversation::query()
            ->where('id', $conversationId)
            ->where('participant_type', Conversation::participantType($participant))
            ->where('participant_id', Conversation::participantKey($participant))
            ->whereNull($column)
            ->update([$column => $workspaceId]);
    }

    public function ensureMode(string $conversationId, object $participant, ChatMode $mode): void
    {
        if (! $this->columnAvailable('mode')) {
            return;
        }

        Conversation::query()
            ->where('id', $conversationId)
            ->where('participant_type', Conversation::participantType($participant))
            ->where('participant_id', Conversation::participantKey($participant))
            ->update(['mode' => $mode->value]);
    }

    public function workspaceMatchesCurrent(?string $conversationId): bool
    {
        $column = FilamentAi::workspaceColumn();

        if ($conversationId === null || $conversationId === '' || ! $this->columnAvailable($column)) {
            return true;
        }

        $conversation = Conversation::query()->find($conversationId);

        if ($conversation === null || $conversation->workspaceKey() === null) {
            return true;
        }

        $current = FilamentAi::workspaceId();

        if ($current === null) {
            return true;
        }

        return (string) $conversation->workspaceKey() === (string) $current;
    }

    public function userCanAccess(Conversation $conversation): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if (
            $conversation->participant_type !== Conversation::participantType($user)
            || (string) $conversation->participant_id !== (string) Conversation::participantKey($user)
        ) {
            return false;
        }

        return $this->workspaceMatchesCurrent($conversation->id);
    }

    protected function columnAvailable(string $column): bool
    {
        $table = config('ai.conversations.tables.conversations', 'agent_conversations');

        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
