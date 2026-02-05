<?php

namespace BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages;

use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedbackItem extends ViewRecord
{
    protected static string $resource = FeedbackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
