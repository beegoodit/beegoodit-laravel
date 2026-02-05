<?php

namespace BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages;

use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackItem extends EditRecord
{
    protected static string $resource = FeedbackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
