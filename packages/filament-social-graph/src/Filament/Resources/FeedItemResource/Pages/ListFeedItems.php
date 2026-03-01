<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedItems extends ListRecords
{
    protected static string $resource = FeedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
