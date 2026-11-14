<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use BeegoodIT\FilamentAi\Models\Conversation;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\ConversationHistoryService;
use BeegoodIT\FilamentAi\Support\ChatMode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class Conversations extends Page implements HasTable
{
    use InteractsWithTable;

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

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = app(ConversationHistoryService::class)->countForCurrentUser();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.conversations.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.conversations.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->conversationsQuery())
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-ai::ai.pages.conversations.columns.title'))
                    ->placeholder(__('filament-ai::ai.pages.conversations.untitled'))
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('mode')
                    ->label(__('filament-ai::ai.chat.mode'))
                    ->formatStateUsing(function (mixed $state): string {
                        $mode = $state instanceof ChatMode
                            ? $state
                            : ChatMode::tryFromConfig(is_string($state) ? $state : null);

                        return __('filament-ai::ai.chat.modes.'.$mode->value);
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label(__('filament-ai::ai.pages.conversations.columns.messages'))
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('filament-ai::ai.pages.conversations.columns.updated_at'))
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(fn (Conversation $record): string => ChatConversation::getUrl([
                'conversation' => $record->getKey(),
            ]))
            ->recordActions([
                Action::make('open')
                    ->label(__('filament-ai::ai.pages.conversations.open'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (Conversation $record): string => ChatConversation::getUrl([
                        'conversation' => $record->getKey(),
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('filament-ai::ai.pages.conversations.empty'))
            ->emptyStateDescription(null)
            ->emptyStateActions([
                Action::make('newChat')
                    ->label(__('filament-ai::ai.pages.conversations.new'))
                    ->url(Assistant::getUrl()),
            ]);
    }

    protected function conversationsQuery(): Builder
    {
        return app(ConversationHistoryService::class)->queryForCurrentUser()
            ?? Conversation::query()->whereKey([]);
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
