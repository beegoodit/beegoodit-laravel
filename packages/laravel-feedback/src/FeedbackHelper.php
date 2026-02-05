<?php

namespace BeegoodIT\LaravelFeedback;

use BeegoodIT\LaravelFeedback\Filament\Pages\FeedbackPage;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource;
use Filament\Navigation\MenuItem;

class FeedbackHelper
{
    /**
     * Get the feedback menu item for user menu.
     */
    public static function getUserMenuItem(): MenuItem
    {
        return MenuItem::make()
            ->label(__('feedback::feedback.menu_item'))
            ->icon('heroicon-o-chat-bubble-left')
            ->url(fn (): string => FeedbackPage::getUrl())
            ->sort(2);
    }

    /**
     * Get the feedback resource class (for Admin panel).
     */
    public static function getResourceClass(): string
    {
        return FeedbackItemResource::class;
    }

    /**
     * Get the feedback page class (for all panels).
     */
    public static function getPageClass(): string
    {
        return FeedbackPage::class;
    }
}
