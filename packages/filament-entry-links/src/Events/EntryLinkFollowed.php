<?php

namespace BeegoodIT\FilamentEntryLinks\Events;

use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class EntryLinkFollowed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public EntryLink $entryLink,
        public Request $request,
    ) {}
}
