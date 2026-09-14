<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource\Pages;

use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Poll;

class ViewHistoryRun extends ViewRecord
{
    protected static string $resource = HistoryResource::class;

    #[Poll(3)]
    public function refreshRunStatus(): void
    {
        /** @var OperationRun $record */
        $record = $this->getRecord();

        if ($record->isTerminal()) {
            return;
        }

        $this->record->refresh();
    }
}
