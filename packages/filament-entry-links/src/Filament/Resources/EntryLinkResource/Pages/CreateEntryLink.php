<?php

namespace BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource\Pages;

use BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode;
use BeegoodIT\FilamentEntryLinks\Filament\Resources\EntryLinkResource;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateEntryLink extends CreateRecord
{
    protected static string $resource = EntryLinkResource::class;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill([
            'token' => Str::lower(Str::random(8)),
            'active_from' => EntryLink::defaultOpenActiveFrom(),
            'active_to' => EntryLink::defaultOpenActiveTo(),
            'redirect_code' => EntryLinkRedirectCode::Temporary->value,
            'is_enabled' => true,
        ]);

        $this->callHook('afterFill');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! isset($data['token']) || $data['token'] === '' || $data['token'] === null) {
            $data['token'] = Str::lower(Str::random(8));
        }

        if (empty($data['active_from'])) {
            $data['active_from'] = EntryLink::defaultOpenActiveFrom();
        }

        if (empty($data['active_to'])) {
            $data['active_to'] = EntryLink::defaultOpenActiveTo();
        }

        return $data;
    }
}
