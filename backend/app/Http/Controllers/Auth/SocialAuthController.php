<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /** Redirect to Google's OAuth consent screen. */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     * - Existing user with google_id → log in.
     * - Existing user by email (registered by email) → link account + log in.
     * - New user → create as renter (listers still need ID verification).
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link google_id if signing in via email account for the first time.
            if (! $user->google_id) {
                $user->update([
                    'google_id'  => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                ]);
            }
        } else {
            $user = User::create([
                'google_id'   => $googleUser->getId(),
                'name'        => $googleUser->getName(),
                'email'       => $googleUser->getEmail(),
                'avatar_url'  => $googleUser->getAvatar(),
                'password'    => bcrypt(Str::random(32)), // unusable — Google auth only
                'role'        => 'renter',
                'tos_accepted_at' => now(),
                'referral_code'   => $this->uniqueReferralCode(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('browse'));
    }

    private function uniqueReferralCode(): string
    {
        do { $code = strtoupper(Str::random(8)); }
        while (User::where('referral_code', $code)->exists());
        return $code;
    }
}
