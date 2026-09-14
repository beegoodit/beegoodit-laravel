<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Concerns\ValidatesQualificationActivityWindow;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQualification extends CreateRecord
{
    use ValidatesQualificationActivityWindow;

    protected static string $resource = QualificationResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assertWithinActivityWindow($data);

        return $data;
    }
}
