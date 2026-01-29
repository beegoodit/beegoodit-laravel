<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource\Pages;

use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialLinks extends ListRecords
{
    protected static string $resource = SocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
