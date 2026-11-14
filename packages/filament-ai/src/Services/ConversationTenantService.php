<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use BeegoodIT\FilamentAi\Support\SubjectScope;
use Illuminate\Support\Facades\Schema;

class ConversationTenantService
{
    public function ensureTenantScope(string $conversationId, object $participant): void
    {
        if (! $this->columnAvailable('subject_id') || ! $this->columnAvailable('subject_type')) {
            return;
        }

        $subject = FilamentAi::subject();

        if ($subject === null) {
            return;
        }

        Conversation::query()
            ->where('id', $conversationId)
            ->forParticipant($participant)
            ->whereNull('subject_type')
            ->whereNull('subject_id')
            ->update(SubjectScope::attributes($subject));
    }

    public function ensureMode(string $conversationId, object $participant, ChatMode $mode): void
    {
        if (! $this->columnAvailable('mode')) {
            return;
        }

        Conversation::query()
            ->where('id', $conversationId)
            ->forParticipant($participant)
            ->update(['mode' => $mode->value]);
    }

    public function tenantMatchesCurrent(?string $conversationId): bool
    {
        if ($conversationId === null || $conversationId === '' || ! $this->columnAvailable('subject_id')) {
            return true;
        }

        $conversation = Conversation::query()->find($conversationId);

        if ($conversation === null || $conversation->subjectKey() === null || $conversation->subjectType() === null) {
            return true;
        }

        $current = FilamentAi::subject();

        if ($current === null) {
            return true;
        }

        return $conversation->subjectType() === $current->getMorphClass()
            && $conversation->subjectKey() === (string) $current->getKey();
    }

    public function userCanAccess(Conversation $conversation): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if (! $conversation->belongsToParticipant($user)) {
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
