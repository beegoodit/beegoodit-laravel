<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Concerns;

use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ChatAttachmentMapper;
use BeegoodIT\FilamentAi\Services\ChatTurnCancellation;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use BeegoodIT\FilamentAi\Services\ConversationWorkspaceService;
use BeegoodIT\FilamentAi\Services\PromptService;
use BeegoodIT\FilamentAi\Services\ProviderErrorPresenter;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Ai\Streaming\Events\TextDelta;
use Throwable;

trait InteractsWithChat
{
    public string $prompt = '';

    public string $chatMode = 'ask';

    public ?string $conversationId = null;

    /** @var list<array{role: string, content: string, attachments?: list<array{name: string, type: string, path: string, disk: string}>}> */
    public array $messages = [];

    public string $streamingReply = '';

    public ?string $activeTurnId = null;

    /** @var list<string> */
    public array $attachments = [];

    public int $attachmentUploadKey = 0;

    public function initializeAiChat(AccessService $access, ?ConversationHistoryService $history = null): void
    {
        abort_unless($access->canUseAssistant(), 403);

        $this->chatMode = ChatMode::tryFromConfig()->value;

        if ($this->conversationId !== null && $history !== null) {
            $conversation = $history->findAccessible($this->conversationId);

            if ($conversation === null) {
                abort(404);
            }

            $this->messages = $history->toUiMessages($conversation);
            $this->chatMode = $conversation->chatMode()->value;
        }
    }

    public function form(Schema $schema): Schema
    {
        $mapper = app(ChatAttachmentMapper::class);
        $maxFiles = (int) config('filament-ai.attachments.max_files', 10);
        $maxSizeKb = (int) config('filament-ai.attachments.max_size_kb', 25600);
        $directory = $mapper->directory();

        return $schema
            ->components([
                Textarea::make('prompt')
                    ->hiddenLabel()
                    ->placeholder(__('filament-ai::ai.chat.prompt_placeholder'))
                    ->rows(3)
                    ->autosize(),
                FileUpload::make('attachments')
                    ->hiddenLabel()
                    ->multiple()
                    ->maxFiles($maxFiles)
                    ->maxSize($maxSizeKb)
                    ->disk($mapper->disk())
                    ->directory($directory)
                    ->visibility('private')
                    ->storeFiles()
                    ->helperText(__('filament-ai::ai.chat.attachments_help', [
                        'max' => $maxFiles,
                        'size' => (int) ceil($maxSizeKb / 1024),
                    ]))
                    ->extraFieldWrapperAttributes(fn (): array => [
                        'wire:key' => 'filament-ai-attachments-'.$this->attachmentUploadKey,
                    ]),
                Select::make('chatMode')
                    ->hiddenLabel()
                    ->options([
                        ChatMode::Ask->value => __('filament-ai::ai.chat.modes.ask'),
                        ChatMode::Plan->value => __('filament-ai::ai.chat.modes.plan'),
                        ChatMode::Agent->value => __('filament-ai::ai.chat.modes.agent'),
                    ])
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state): void {
                        $this->persistChatMode($state);
                    }),
            ]);
    }

    public function sendMessage(
        PromptService $promptService,
        ConversationWorkspaceService $workspaceService,
        ChatAttachmentMapper $attachmentMapper,
        ChatTurnCancellation $turnCancellation,
        ProviderErrorPresenter $errorPresenter,
    ): void {
        $message = trim($this->prompt);
        $attachmentPaths = $this->persistAndCollectAttachmentPaths();

        if ($message === '' && $attachmentPaths === []) {
            Notification::make()
                ->title(__('filament-ai::ai.chat.send_empty_title'))
                ->warning()
                ->send();

            return;
        }

        if (! $workspaceService->workspaceMatchesCurrent($this->conversationId)) {
            Notification::make()
                ->title(__('filament-ai::ai.chat.workspace_mismatch_title'))
                ->body(__('filament-ai::ai.chat.workspace_mismatch_body'))
                ->danger()
                ->send();

            return;
        }

        $mode = ChatMode::tryFrom($this->chatMode) ?? ChatMode::tryFromConfig();
        $files = $attachmentMapper->fromStoragePaths($attachmentPaths);
        $uiAttachments = array_map(function ($file) use ($attachmentMapper): array {
            $array = method_exists($file, 'toArray') ? $file->toArray() : [];

            return [
                'name' => (string) ($array['name'] ?? 'file'),
                'type' => (string) ($array['type'] ?? 'stored-document'),
                'path' => (string) ($array['path'] ?? ''),
                'disk' => (string) ($array['disk'] ?? $attachmentMapper->disk()),
            ];
        }, $files);

        $promptForModel = $message !== '' ? $message : __('filament-ai::ai.chat.attachments_only_prompt');
        $attachmentContext = $attachmentMapper->promptContextBlock($uiAttachments);

        if ($attachmentContext !== '') {
            $promptForModel = trim($promptForModel."\n\n".$attachmentContext);
        }

        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
            'attachments' => $uiAttachments,
        ];
        $this->clearComposer($mode);
        $this->streamingReply = '';

        $user = auth()->user();

        if ($user === null) {
            return;
        }

        if ($this->conversationId !== null) {
            $workspaceService->ensureWorkspaceScope($this->conversationId, $user);
            $workspaceService->ensureMode($this->conversationId, $user, $mode);
        }

        app()->instance(
            ToolContext::class,
            ToolContext::fromRequest($this->conversationId, $mode),
        );

        $this->activeTurnId = (string) Str::uuid();
        $turnCancellation->begin($this->activeTurnId, $user->getAuthIdentifier());
        $cancelled = false;

        try {
            if (config('filament-ai.chat.streaming', true)) {
                $stream = $promptService->stream(
                    $user,
                    $promptForModel,
                    $this->conversationId,
                    $files,
                    $mode,
                );

                foreach ($stream as $event) {
                    if ($turnCancellation->shouldStop((string) $this->activeTurnId)) {
                        $cancelled = true;
                        break;
                    }

                    if ($event instanceof TextDelta) {
                        $this->streamingReply .= $event->delta;

                        if (method_exists($this, 'stream')) {
                            $this->stream(to: 'streamingReply', content: $event->delta, replace: false);
                        }
                    }
                }

                $this->conversationId = $stream->conversationId ?? $this->conversationId;
            } else {
                $response = $promptService->prompt(
                    $user,
                    $promptForModel,
                    $this->conversationId,
                    $files,
                    $mode,
                );
                $this->streamingReply = (string) $response;
                $this->conversationId = $response->conversationId ?? $this->conversationId;
            }

            if ($this->conversationId !== null) {
                $workspaceService->ensureWorkspaceScope($this->conversationId, $user);
                $workspaceService->ensureMode($this->conversationId, $user, $mode);
            }

            $content = $this->streamingReply;

            if ($cancelled) {
                $content = trim($content."\n\n".__('filament-ai::ai.chat.stopped_notice'));
                Notification::make()
                    ->title(__('filament-ai::ai.chat.stop_title'))
                    ->warning()
                    ->send();
            }

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $content,
                'attachments' => [],
            ];
        } catch (Throwable $exception) {
            Notification::make()
                ->title(__('filament-ai::ai.chat.errors.title'))
                ->body($errorPresenter->present($exception))
                ->danger()
                ->send();

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $errorPresenter->present($exception),
                'attachments' => [],
            ];
        } finally {
            if ($this->activeTurnId !== null) {
                $turnCancellation->forget($this->activeTurnId);
            }
            $this->activeTurnId = null;
            $this->streamingReply = '';
        }

        if ($this->shouldRedirectToChatAfterMessage() && $this->conversationId !== null) {
            $this->redirectToConversation($this->conversationId);
        }
    }

    public function stopGeneration(ChatTurnCancellation $turnCancellation): void
    {
        $user = auth()->user();

        if ($user === null || $this->activeTurnId === null) {
            return;
        }

        $turnCancellation->requestStop($this->activeTurnId, $user->getAuthIdentifier());
    }

    protected function persistChatMode(?string $state): void
    {
        $mode = ChatMode::tryFrom((string) $state) ?? ChatMode::tryFromConfig();
        $this->chatMode = $mode->value;

        $user = auth()->user();

        if ($user === null || $this->conversationId === null) {
            return;
        }

        app(ConversationWorkspaceService::class)->ensureMode($this->conversationId, $user, $mode);
    }

    protected function clearComposer(ChatMode $mode): void
    {
        $this->prompt = '';
        $this->attachments = [];
        $this->attachmentUploadKey++;
        $this->chatMode = $mode->value;
    }

    /**
     * @return list<string>
     */
    protected function persistAndCollectAttachmentPaths(): array
    {
        $paths = array_values(array_filter(
            Arr::wrap($this->attachments),
            fn (mixed $path): bool => is_string($path) && $path !== '',
        ));

        $this->attachments = [];

        return $paths;
    }

    protected function shouldRedirectToChatAfterMessage(): bool
    {
        return false;
    }

    protected function redirectToConversation(string $conversationId): void
    {
        //
    }
}
