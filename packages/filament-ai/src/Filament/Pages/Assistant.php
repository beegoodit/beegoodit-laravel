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

class Assistant extends Page implements HasForms
{
    use InteractsWithChat;
    use InteractsWithForms {
        InteractsWithChat::form insteadof InteractsWithForms;
    }

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament-ai::pages.assistant';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return config('filament-ai.navigation_icon', 'heroicon-o-sparkles');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-ai::ai.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-ai::ai.navigation.assistant');
    }

    public static function canAccess(): bool
    {
        return app(AccessService::class)->canUseAssistant();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(AccessService $access, ConversationHistoryService $history): void
    {
        $conversationId = request()->query('conversation');

        if (is_string($conversationId) && $conversationId !== '') {
            if ($history->findAccessible($conversationId) === null) {
                abort(404);
            }

            $this->redirect(ChatConversation::getUrl(['conversation' => $conversationId]));

            return;
        }

        $this->initializeAiChat($access);
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.assistant.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.assistant.heading');
    }

    protected function shouldRedirectToChatAfterMessage(): bool
    {
        return $this->conversationId !== null;
    }

    protected function redirectToConversation(string $conversationId): void
    {
        $this->redirect(ChatConversation::getUrl(['conversation' => $conversationId]));
    }
}
