<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use BeegoodIT\FilamentAi\Filament\Concerns\InteractsWithChat;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ChatConversation extends Page implements HasForms
{
    use InteractsWithChat;
    use InteractsWithForms {
        InteractsWithChat::form insteadof InteractsWithForms;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-ai::pages.assistant';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(AccessService $access, ConversationHistoryService $history, string $conversation): void
    {
        $this->conversationId = $conversation;
        $this->initializeAiChat($access, $history);
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'ai-conversations/{conversation}';
    }

    public static function canAccess(): bool
    {
        return app(AccessService::class)->canUseAssistant();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->chatHeading();
    }

    public function getHeading(): string|Htmlable
    {
        return $this->chatHeading();
    }
}
