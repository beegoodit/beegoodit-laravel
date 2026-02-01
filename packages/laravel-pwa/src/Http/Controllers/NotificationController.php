<?php

namespace BeegoodIT\LaravelPwa\Http\Controllers;

use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function trackOpen(Message $message): JsonResponse
    {
        if (! $message->opened_at) {
            $message->update(['opened_at' => now()]);
            $message->broadcast()->increment('total_opened');
        }

        return response()->json(['success' => true]);
    }
}
