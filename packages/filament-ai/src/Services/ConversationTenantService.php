<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Support\Facades\Schema;

class ConversationTenantService
{
    public function ensureTenantScope(string $conversationId, object $participant): void
    {
        $column = FilamentAi::tenantColumn();

        if (! $this->columnAvailable($column)) {
            return;
        }

        $tenantId = FilamentAi::tenantId();

        if ($tenantId === null) {
            return;
        }

        Conversation::query()
            ->where('id', $conversationId)
            ->where('participant_type', Conversation::participantType($participant))
            ->where('participant_id', Conversation::participantKey($participant))
            ->whereNull($column)
            ->update([$column => $tenantId]);
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

    public function tenantMatchesCurrent(?string $conversationId): bool
    {
        $column = FilamentAi::tenantColumn();

        if ($conversationId === null || $conversationId === '' || ! $this->columnAvailable($column)) {
            return true;
        }

        $conversation = Conversation::query()->find($conversationId);

        if ($conversation === null || $conversation->tenantKey() === null) {
            return true;
        }

        $current = FilamentAi::tenantId();

        if ($current === null) {
            return true;
        }

        return (string) $conversation->tenantKey() === (string) $current;
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

        return $this->tenantMatchesCurrent($conversation->id);
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
