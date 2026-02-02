<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\PushSubscriptionResource\Pages;

use BeegoodIT\LaravelPwa\Filament\Resources\PushSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListPushSubscriptions extends ListRecords
{
    protected static string $resource = PushSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
