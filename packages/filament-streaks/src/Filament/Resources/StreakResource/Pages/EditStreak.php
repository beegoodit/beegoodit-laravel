<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Concerns\ValidatesOpenStreakUniqueness;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStreak extends EditRecord
{
    use ValidatesOpenStreakUniqueness;

    protected static string $resource = StreakResource::class;

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
        $this->assertNoOpenDuplicate($data, $this->getRecord());

        return $data;
    }
}
