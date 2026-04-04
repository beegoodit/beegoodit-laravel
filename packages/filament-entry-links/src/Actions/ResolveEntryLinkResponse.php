<?php

namespace BeegoodIT\FilamentEntryLinks\Actions;

use BeegoodIT\FilamentEntryLinks\Events\EntryLinkFollowed;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use BeegoodIT\FilamentEntryLinks\Support\PublicEntryViews;
use BeegoodIT\FilamentEntryLinks\Support\TargetUrlValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class ResolveEntryLinkResponse
{
    public function __construct(
        private TargetUrlValidator $targetUrlValidator,
    ) {}

    public function handle(Request $request, string $token): Response
    {
        if ($token === '') {
            return $this->unavailableResponse();
        }

        /** @var EntryLink|null $link */
        $link = EntryLink::query()->where('token', $token)->first();

        if ($link === null) {
            return $this->unavailableResponse();
        }

        if (! $link->is_enabled) {
            return $this->unavailableResponse();
        }

        if (! $link->isOpenEndedActiveFrom() && now()->lt($link->active_from)) {
            return response()
                ->view(PublicEntryViews::comingSoon(), [
                    'activeFrom' => $link->active_from,
                    'homeUrl' => $this->homeUrl(),
                ], Response::HTTP_OK);
        }

        if (! $link->isOpenEndedActiveTo() && now()->gt($link->active_to)) {
            return $this->unavailableResponse();
        }

        if (! $this->targetUrlValidator->allows($link->target_url)) {
            return $this->unavailableResponse();
        }

        Event::dispatch(new EntryLinkFollowed($link, $request));

        return redirect()->away($link->target_url, $link->redirect_code->value);
    }

    private function unavailableResponse(): Response
    {
        return response()
            ->view(PublicEntryViews::unavailable(), [
                'homeUrl' => $this->homeUrl(),
            ], Response::HTTP_NOT_FOUND);
    }

    private function homeUrl(): string
    {
        $configured = config('filament-entry-links.home_url');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return url('/');
    }
}
