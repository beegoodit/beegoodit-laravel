<?php

namespace BeegoodIT\FilamentEntryLinks\Http\Controllers;

use BeegoodIT\FilamentEntryLinks\Actions\ResolveEntryLinkResponse;
use BeegoodIT\FilamentEntryLinks\Http\Requests\ShowEntryLinkRequest;
use BeegoodIT\FilamentEntryLinks\Support\SegmentTokenParser;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ShowEntryLinkController
{
    public function __invoke(
        ShowEntryLinkRequest $request,
        ResolveEntryLinkResponse $resolve,
    ): Response|SymfonyResponse {
        $segment = $request->route('segment');

        if (! is_string($segment)) {
            $segment = '';
        }

        $token = SegmentTokenParser::tokenFromSegment($segment);

        return $resolve->handle($request, $token);
    }
}
