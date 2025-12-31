<?php

namespace BeeGoodIT\FilamentLegal\Http\Controllers;

use BeeGoodIT\FilamentLegal\Models\LegalPolicy;
use BeeGoodIT\FilamentLegal\Models\PolicyAcceptance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LegalAcceptanceController extends Controller
{
    public function show()
    {
        $policy = LegalPolicy::getActive('privacy');

        if (!$policy) {
            return redirect()->intended();
        }

        return view('filament-legal::acceptance', compact('policy'));
    }

    public function accept(Request $request)
    {
        $policy = LegalPolicy::getActive('privacy');

        if (!$policy) {
            return redirect()->intended();
        }

        PolicyAcceptance::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'legal_policy_id' => $policy->id,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accepted_at' => now(),
            ]
        );

        return redirect()->intended();
    }
}
