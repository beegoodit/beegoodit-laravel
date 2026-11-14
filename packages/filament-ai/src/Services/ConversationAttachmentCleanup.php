<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Conversation;
use Illuminate\Support\Facades\Storage;

class ConversationAttachmentCleanup
{
    public function deleteForConversation(Conversation $conversation): void
    {
        foreach ($conversation->messages()->get(['attachments']) as $message) {
            $this->deleteFromAttachmentsPayload($message->attachments ?? null);
        }
    }

    public function deleteFromAttachmentsPayload(mixed $payload): void
    {
        foreach ($this->pathsFromPayload($payload) as $item) {
            $this->deleteQuietly($item['path'], $item['disk']);
        }
    }

    /**
     * @return list<array{path: string, disk: string}>
     */
    public function pathsFromPayload(mixed $payload): array
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
            $disk = $attachment['disk'] ?? config('filesystems.default', 'local');

            if (! is_string($path) || $path === '' || ! is_string($disk) || $disk === '') {
                continue;
            }

            $items[] = ['path' => $path, 'disk' => $disk];
        }

        return $items;
    }

    protected function deleteQuietly(string $path, string $disk): void
    {
        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable) {
            // Fail soft — object may already be gone.
        }
    }
}
