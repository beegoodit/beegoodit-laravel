<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Models;

use BeegoodIT\FilamentAi\Services\ConversationAttachmentCleanup;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\SubjectScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
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

    public function subjectKey(): ?string
    {
        $id = $this->getAttribute('subject_id');

        return $id === null || $id === '' ? null : (string) $id;
    }

    public function subjectType(): ?string
    {
        $type = $this->getAttribute('subject_type');

        return is_string($type) && $type !== '' ? $type : null;
    }

    public function tenantKey(): int|string|null
    {
        return $this->subjectKey();
    }

    public function assignSubject(?Model $subject): static
    {
        $this->forceFill(SubjectScope::attributes($subject));

        return $this;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForParticipant(Builder $query, object $participant): Builder
    {
        if (static::usesParticipantColumns()) {
            return $query
                ->where('participant_type', static::participantType($participant))
                ->where('participant_id', static::participantKey($participant));
        }

        return $query->where('user_id', static::participantKey($participant));
    }

    public function belongsToParticipant(object $participant): bool
    {
        if (static::usesParticipantColumns()) {
            return $this->participant_type === static::participantType($participant)
                && (string) $this->participant_id === (string) static::participantKey($participant);
        }

        return (string) $this->getAttribute('user_id') === (string) static::participantKey($participant);
    }

    public static function participantType(object $participant): string
    {
        if (method_exists(AgentConversation::class, 'participantType')) {
            return AgentConversation::participantType($participant);
        }

        return $participant instanceof Model
            ? $participant->getMorphClass()
            : $participant::class;
    }

    public static function participantKey(object $participant): string|int
    {
        if (method_exists(AgentConversation::class, 'participantKey')) {
            return AgentConversation::participantKey($participant);
        }

        if ($participant instanceof Model) {
            return $participant->getKey();
        }

        return $participant->id ?? throw new InvalidArgumentException(
            'The conversation participant must be an Eloquent model or expose an [id] property.'
        );
    }

    public static function usesParticipantColumns(): bool
    {
        $table = (new static)->getTable();

        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, 'participant_type');
        } catch (\Throwable) {
            return false;
        }
    }
}
