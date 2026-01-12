<?php

namespace BeeGoodIT\LaravelPwa\Http\Controllers;

use BeeGoodIT\LaravelPwa\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        \Log::debug('PushSubscriptionController@store', [
            'endpoint' => $request->input('endpoint'),
            'user_id' => Auth::id(),
            'method' => $request->method(),
        ]);

        $request->validate([
            'endpoint' => 'required|string|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $model = config('pwa.subscription_model', PushSubscription::class);

        $subscription = $model::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'user_id' => Auth::id(),
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
            ]
        );

        return response()->json([
            'success' => true,
            'subscription_id' => $subscription->id,
        ], 201);
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $model = config('pwa.subscription_model', PushSubscription::class);

        $deleted = $model::where('endpoint', $request->input('endpoint'))
            ->when(Auth::check(), fn($q) => $q->where('user_id', Auth::id()))
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
        ]);
    }
}
