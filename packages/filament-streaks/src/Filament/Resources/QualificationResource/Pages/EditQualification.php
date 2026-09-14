<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Concerns\ValidatesQualificationActivityWindow;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQualification extends EditRecord
{
    use ValidatesQualificationActivityWindow;

    protected static string $resource = QualificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertWithinActivityWindow($data);

        return $data;
    }
}
