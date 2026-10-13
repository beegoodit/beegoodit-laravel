<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Agents\ShellAgent;
use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ConversationHistoryService
{
    public function __construct(
        private ConversationWorkspaceService $workspaceService,
    ) {}

    public function findAccessible(string $conversationId): ?Conversation
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        $conversation = Conversation::query()
            ->where('id', $conversationId)
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->whereHas('messages', fn (Builder $query): Builder => $query->where('agent', ShellAgent::class))
            ->first();

        if ($conversation === null || ! $this->workspaceService->userCanAccess($conversation)) {
            return null;
        }

        return $conversation;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function listForCurrentUser(int $limit = 50): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        $column = FilamentAi::workspaceColumn();
        $workspaceId = FilamentAi::workspaceId();

        return Conversation::query()
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->whereHas('messages', fn (Builder $query): Builder => $query->where('agent', ShellAgent::class))
            ->when(
                $workspaceId === null,
                fn (Builder $query) => $query->whereNull($column),
                fn (Builder $query) => $query->where($column, $workspaceId),
            )
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array{role: string, content: string, attachments: list<array{name: string, type: string, path: string, disk: string}>}>
     */
    public function toUiMessages(Conversation $conversation): array
    {
        return $conversation->messages()
            ->where('agent', ShellAgent::class)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get(['role', 'content', 'attachments'])
            ->map(fn ($message): array => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
                'attachments' => $this->attachmentsForUi($message->attachments ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{name: string, type: string, path: string, disk: string}>
     */
    protected function attachmentsForUi(mixed $payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($payload)) {
            return [];
        }

        $items = [];

        foreach ($payload as $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            $path = $attachment['path'] ?? null;

            if (! is_string($path) || $path === '') {
                continue;
            }

            $items[] = [
                'name' => (string) ($attachment['name'] ?? basename($path)),
                'type' => (string) ($attachment['type'] ?? 'stored-document'),
                'path' => $path,
                'disk' => (string) ($attachment['disk'] ?? config('filesystems.default', 'local')),
            ];
        }

        return $items;
    }
}
