<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AgeVerificationController extends Controller
{
    /**
     * Display the age verification page.
     */
    public function show(): View
    {
        return view('auth.verify-age');
    }

    /**
     * Handle the age verification submission.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'confirm_age' => ['required', 'accepted'],
        ], [
            'confirm_age.accepted' => 'You must confirm that you are at least 21 years old to continue.',
        ]);

        if (! $validated['confirm_age']) {
            return back()->with('error', 'You must confirm that you are at least 21 years old to continue.');
        }

        // Set age verification cookie for 7 days (10080 minutes)
        Cookie::queue('age_verified', true, 10080);

        // Redirect to the intended URL or default to catalog
        $redirectUrl = session('intended_url') ?? route('inventory.catalog');
        session()->forget('intended_url');

        return redirect($redirectUrl);
    }
}
