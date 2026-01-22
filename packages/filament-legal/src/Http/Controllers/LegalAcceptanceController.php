<?php

namespace BeegoodIT\FilamentLegal\Http\Controllers;

use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use BeegoodIT\FilamentLegal\Models\PolicyAcceptance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LegalAcceptanceController extends Controller
{
    public function show()
    {
        $policy = LegalPolicy::getActive('privacy');

        if (!$policy instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy) {
            return redirect()->intended();
        }

        return view('filament-legal::acceptance', ['policy' => $policy]);
    }

    public function accept(Request $request)
    {
        $policy = LegalPolicy::getActive('privacy');

        if (!$policy instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy) {
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
