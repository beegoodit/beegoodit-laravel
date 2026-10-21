<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Concerns;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ChatAttachmentMapper;
use BeegoodIT\FilamentAi\Services\ChatTurnCancellation;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use BeegoodIT\FilamentAi\Services\ConversationTenantService;
use BeegoodIT\FilamentAi\Services\PromptService;
use BeegoodIT\FilamentAi\Services\ProviderErrorPresenter;
use BeegoodIT\FilamentAi\Services\ToolContext;
use BeegoodIT\FilamentAi\Support\ChatMarkdown;
use BeegoodIT\FilamentAi\Support\ChatMode;
use BeegoodIT\FilamentAi\Support\ChatModels;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Ai\Streaming\Events\TextDelta;
use Throwable;

trait InteractsWithChat
{
    public string $prompt = '';

    public string $chatMode = 'ask';

    public string $chatModel = '';

    public ?string $conversationId = null;

    public ?string $conversationTitle = null;

    public ?string $conversationCreatedAt = null;

    public ?string $conversationUpdatedAt = null;

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
        $this->chatModel = $this->resolveInitialChatModel();

        if ($this->conversationId !== null && $history !== null) {
            $conversation = $history->findAccessible($this->conversationId);

            if ($conversation === null) {
                abort(404);
            }

            $this->messages = $history->toUiMessages($conversation);
            $this->chatMode = $conversation->chatMode()->value;
            $this->syncConversationMeta($conversation);
        } else {
            $this->syncConversationMeta(null);
        }
    }

    public function chatHeading(): string
    {
        if (filled($this->conversationTitle)) {
            return (string) $this->conversationTitle;
        }

        return __('filament-ai::ai.chat.new_chat');
    }

    /**
     * @return list<array{name: string, path: string}>
     */
    public function conversationAttachments(): array
    {
        $items = [];

        foreach ($this->messages as $message) {
            foreach ($message['attachments'] ?? [] as $attachment) {
                if (! is_array($attachment)) {
                    continue;
                }

                $name = (string) ($attachment['name'] ?? basename((string) ($attachment['path'] ?? '')));

                if ($name === '') {
                    continue;
                }

                $items[] = [
                    'name' => $name,
                    'path' => (string) ($attachment['path'] ?? ''),
                ];
            }
        }

        return $items;
    }

    public function form(Schema $schema): Schema
    {
        $mapper = app(ChatAttachmentMapper::class);
        $maxFiles = (int) config('filament-ai.attachments.max_files', 10);
        $maxSizeKb = (int) config('filament-ai.attachments.max_size_kb', 25600);
        $directory = $mapper->directory();

        return $schema
            ->components([
                Flex::make([
                    Textarea::make('prompt')
                        ->hiddenLabel()
                        ->placeholder(__('filament-ai::ai.chat.prompt_placeholder'))
                        ->rows(3)
                        ->autosize()
                        ->extraInputAttributes([
                            'x-on:keydown.ctrl.enter.prevent' => '$event.target.closest(\'form\').requestSubmit()',
                            'x-on:keydown.meta.enter.prevent' => '$event.target.closest(\'form\').requestSubmit()',
                        ])
                        ->extraFieldWrapperAttributes([
                            'class' => 'filament-ai-composer-prompt min-w-0 flex-1',
                        ]),
                    FileUpload::make('attachments')
                        ->hiddenLabel()
                        ->multiple()
                        ->maxFiles($maxFiles)
                        ->maxSize($maxSizeKb)
                        ->disk($mapper->disk())
                        ->directory($directory)
                        ->visibility('private')
                        ->storeFiles()
                        ->placeholder($this->attachmentsIdleLabel())
                        ->grow(false)
                        ->extraFieldWrapperAttributes(fn (): array => [
                            'class' => 'filament-ai-composer-attachments',
                            'wire:key' => 'filament-ai-attachments-'.$this->attachmentUploadKey,
                        ]),
                ])
                    ->from('md')
                    ->extraAttributes([
                        'class' => 'filament-ai-composer-input-row w-full',
                    ]),
                Flex::make([
                    Flex::make([
                        Select::make('chatMode')
                            ->hiddenLabel()
                            ->options([
                                ChatMode::Ask->value => __('filament-ai::ai.chat.modes.ask'),
                                ChatMode::Plan->value => __('filament-ai::ai.chat.modes.plan'),
                                ChatMode::Agent->value => __('filament-ai::ai.chat.modes.agent'),
                            ])
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->position('top')
                            ->live()
                            ->afterStateUpdated(function (?string $state): void {
                                $this->persistChatMode($state);
                            })
                            ->grow(false)
                            ->extraFieldWrapperAttributes([
                                'class' => 'w-auto',
                            ]),
                        Select::make('chatModel')
                            ->hiddenLabel()
                            ->options(fn (): array => ChatModels::options($this->chatModel))
                            ->searchable()
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->position('top')
                            ->wrapOptionLabels(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state): void {
                                $this->persistChatModel($state);
                            })
                            ->grow(false)
                            ->extraFieldWrapperAttributes([
                                'class' => 'filament-ai-composer-model',
                                'style' => 'width: 18rem; min-width: 18rem;',
                            ]),
                    ])
                        ->verticalAlignment(VerticalAlignment::Center)
                        ->grow(false)
                        ->extraAttributes([
                            'class' => 'filament-ai-composer-controls gap-2',
                        ]),
                    SchemaView::make('filament-ai::pages.partials.chat-send-button')
                        ->grow(false),
                ])
                    ->alignment(Alignment::Between)
                    ->verticalAlignment(VerticalAlignment::Center)
                    ->extraAttributes([
                        'class' => 'w-full',
                    ]),
            ]);
    }

    public function sendMessage(
        PromptService $promptService,
        ConversationTenantService $tenantService,
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

        if (! $tenantService->tenantMatchesCurrent($this->conversationId)) {
            Notification::make()
                ->title(__('filament-ai::ai.chat.tenant_mismatch_title'))
                ->body(__('filament-ai::ai.chat.tenant_mismatch_body'))
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
            $tenantService->ensureTenantScope($this->conversationId, $user);
            $tenantService->ensureMode($this->conversationId, $user, $mode);
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
                    $this->resolvedChatModel(),
                );

                foreach ($stream as $event) {
                    if ($turnCancellation->shouldStop((string) $this->activeTurnId)) {
                        $cancelled = true;
                        break;
                    }

                    if ($event instanceof TextDelta) {
                        $this->streamingReply .= $event->delta;

                        if (method_exists($this, 'stream')) {
                            $this->stream(
                                to: 'streamingReply',
                                content: ChatMarkdown::toHtml($this->streamingReply),
                                replace: true,
                            );
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
                    $this->resolvedChatModel(),
                );
                $this->streamingReply = (string) $response;
                $this->conversationId = $response->conversationId ?? $this->conversationId;
            }

            if ($this->conversationId !== null) {
                $tenantService->ensureTenantScope($this->conversationId, $user);
                $tenantService->ensureMode($this->conversationId, $user, $mode);
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

            $this->refreshConversationMeta();
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

        app(ConversationTenantService::class)->ensureMode($this->conversationId, $user, $mode);
    }

    protected function persistChatModel(?string $state): void
    {
        $this->chatModel = ChatModels::normalize($state);

        $settings = FilamentAi::settingsModel()::forCurrentTenant();

        if ($settings === null) {
            return;
        }

        $settings->model = ChatModels::toStored($this->chatModel);
        $settings->save();
    }

    protected function resolveInitialChatModel(): string
    {
        $settings = FilamentAi::settingsModel()::forCurrentTenant();
        $fromSettings = $settings?->model;

        if (filled($fromSettings)) {
            return ChatModels::normalize((string) $fromSettings);
        }

        $fallback = config('filament-ai.default_model');

        if (filled($fallback)) {
            return ChatModels::normalize((string) $fallback);
        }

        return ChatModels::Auto;
    }

    protected function resolvedChatModel(): ?string
    {
        return ChatModels::toStored($this->chatModel);
    }

    protected function refreshConversationMeta(): void
    {
        if ($this->conversationId === null) {
            $this->syncConversationMeta(null);

            return;
        }

        $conversation = app(ConversationHistoryService::class)->findAccessible($this->conversationId);

        $this->syncConversationMeta($conversation);
    }

    protected function syncConversationMeta(?Conversation $conversation): void
    {
        if ($conversation === null) {
            $this->conversationTitle = null;
            $this->conversationCreatedAt = null;
            $this->conversationUpdatedAt = null;

            return;
        }

        $title = trim((string) $conversation->title);
        $this->conversationTitle = $title !== '' ? $title : null;
        $this->conversationCreatedAt = $conversation->created_at?->toIso8601String();
        $this->conversationUpdatedAt = $conversation->updated_at?->toIso8601String();
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

    protected function attachmentsIdleLabel(): string
    {
        $browseLabel = e(__('filament-ai::ai.chat.attachments_browse'));

        return <<<HTML
<span class="filepond--label-action filament-ai-file-upload-browse" aria-label="{$browseLabel}" title="{$browseLabel}">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="filament-ai-file-upload-browse-icon">
<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
</svg>
</span>
HTML;
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
