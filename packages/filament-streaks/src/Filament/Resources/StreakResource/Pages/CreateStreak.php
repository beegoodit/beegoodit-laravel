<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Concerns\ValidatesOpenStreakUniqueness;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStreak extends CreateRecord
{
    use ValidatesOpenStreakUniqueness;

    protected static string $resource = StreakResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assertNoOpenDuplicate($data);

        return $data;
    }
}
