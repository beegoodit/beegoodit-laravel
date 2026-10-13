<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class Conversations extends Page
{
    protected static ?int $navigationSort = 20;

    protected string $view = 'filament-ai::pages.conversations';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-ai::ai.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-ai::ai.navigation.conversations');
    }

    public static function canAccess(): bool
    {
        return app(AccessService::class)->canUseAssistant();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.conversations.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.conversations.heading');
    }

    /**
     * @return Collection<int, \BeegoodIT\FilamentAi\Models\Conversation>
     */
    public function getConversationsProperty(): Collection
    {
        return app(ConversationHistoryService::class)->listForCurrentUser();
    }

    public function openConversation(string $conversationId): void
    {
        $this->redirect(ChatConversation::getUrl(['conversation' => $conversationId]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newChat')
                ->label(__('filament-ai::ai.pages.conversations.new'))
                ->url(Assistant::getUrl()),
        ];
    }
}
