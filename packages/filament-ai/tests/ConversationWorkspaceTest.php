<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Services\ChatAttachmentMapper;
use BeegoodIT\FilamentAi\Services\ConversationAttachmentCleanup;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use BeegoodIT\FilamentAi\Services\ConversationWorkspaceService;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class ConversationWorkspaceTest extends TestCase
{
    #[Test]
    public function it_scopes_conversations_to_workspace_from_stub_resolver(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 42,
        ]);

        $user = $this->actingAsUser();

        $conversation = Conversation::query()->create([
            'id' => 'conv-1',
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'title' => 'Hello',
            'mode' => ChatMode::Ask->value,
            'workspace_id' => 42,
        ]);

        $other = Conversation::query()->create([
            'id' => 'conv-2',
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'title' => 'Other',
            'mode' => ChatMode::Ask->value,
            'workspace_id' => 99,
        ]);

        // ShellAgent message so history filters include the conversation.
        $conversation->messages()->create([
            'id' => 'msg-1',
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'agent' => \BeegoodIT\FilamentAi\Agents\ShellAgent::class,
            'role' => 'user',
            'content' => 'hi',
            'attachments' => [],
            'steps' => [],
            'usage' => [],
            'meta' => [],
            'status' => 'completed',
        ]);

        $other->messages()->create([
            'id' => 'msg-2',
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'agent' => \BeegoodIT\FilamentAi\Agents\ShellAgent::class,
            'role' => 'user',
            'content' => 'nope',
            'attachments' => [],
            'steps' => [],
            'usage' => [],
            'meta' => [],
            'status' => 'completed',
        ]);

        $listed = app(ConversationHistoryService::class)->listForCurrentUser();

        $this->assertCount(1, $listed);
        $this->assertSame('conv-1', $listed->first()->id);
        $this->assertNotNull(app(ConversationHistoryService::class)->findAccessible('conv-1'));
        $this->assertNull(app(ConversationHistoryService::class)->findAccessible('conv-2'));
    }

    #[Test]
    public function workspace_service_stamps_mode_and_workspace(): void
    {
        config([
            'filament-ai.workspace_resolver' => fn (): int => 7,
        ]);

        $user = $this->actingAsUser();

        $conversation = Conversation::query()->create([
            'id' => 'conv-stamp',
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'title' => 'Stamp',
        ]);

        $service = app(ConversationWorkspaceService::class);
        $service->ensureWorkspaceScope($conversation->id, $user);
        $service->ensureMode($conversation->id, $user, ChatMode::Agent);

        $conversation->refresh();

        $this->assertSame(7, $conversation->workspaceKey());
        $this->assertSame(ChatMode::Agent, $conversation->chatMode());
    }

    #[Test]
    public function attachment_mapper_uses_configured_disk_and_cleanup_deletes_paths(): void
    {
        Storage::fake('local');

        config([
            'filament-ai.attachments.disk' => 'local',
            'filesystems.default' => 'local',
        ]);

        $path = 'filament-ai/chat-attachments/demo.txt';
        Storage::disk('local')->put($path, 'hello');

        $mapper = app(ChatAttachmentMapper::class);
        $this->assertSame('local', $mapper->disk());

        $files = $mapper->fromStoragePaths([$path]);
        $this->assertCount(1, $files);

        app(ConversationAttachmentCleanup::class)->deleteFromAttachmentsPayload([
            ['path' => $path, 'disk' => 'local', 'name' => 'demo.txt', 'type' => 'stored-document'],
        ]);

        Storage::disk('local')->assertMissing($path);
    }
}
