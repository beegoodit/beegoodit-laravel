<?php

namespace BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages;

use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackItems extends ListRecords
{
    protected static string $resource = FeedbackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
