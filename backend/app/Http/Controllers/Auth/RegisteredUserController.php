<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Handle registration. Enforces:
     *  - role selection (renter/lister) — Page 06
     *  - ToS explicitly accepted, never pre-checked — Constraint 10
     *  - optional referral attribution — Page 28
     *  - listers routed to ID verification; renters to browse — Page 06
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in([Role::Renter->value, Role::Lister->value])],
            // ToS must be explicitly true — cannot be pre-checked (Constraint 10).
            'accept_tos' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ], [
            'accept_tos.accepted' => 'You must accept the Terms of Service, including the off-platform dealing policy.',
        ]);

        $referrer = ! empty($validated['referral_code'])
            ? User::where('referral_code', $validated['referral_code'])->first()
            : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'tos_accepted_at' => now(),
            'referral_code' => $this->uniqueReferralCode(),
            'referred_by' => $referrer?->id,
        ]);

        if ($referrer) {
            Referral::create([
                'referrer_id' => $referrer->id,
                'invited_email' => $user->email,
                'referred_user_id' => $user->id,
                'status' => 'signed_up',
                'reward_type' => 'free_month',
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        // Listers must verify ID before publishing (Constraint 11).
        return $user->role === Role::Lister
            ? redirect()->route('verification.id.show')
            : redirect()->route('browse');
    }

    private function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
