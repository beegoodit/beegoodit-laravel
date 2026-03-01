<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedItem extends ViewRecord
{
    protected static string $resource = FeedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
