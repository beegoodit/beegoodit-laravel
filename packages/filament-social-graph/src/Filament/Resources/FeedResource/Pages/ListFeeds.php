<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeeds extends ListRecords
{
    protected static string $resource = FeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
