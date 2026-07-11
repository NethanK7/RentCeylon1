<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileApiController extends Controller
{
    /** GET /api/me — extended profile for the app. */
    public function show(Request $request)
    {
        $user = $request->user();
        return [
            'id'                       => $user->id,
            'name'                     => $user->name,
            'email'                    => $user->email,
            'phone'                    => $user->phone,
            'role'                     => $user->role->value ?? $user->role,
            'rating_avg'               => $user->rating_avg,
            'rating_count'             => $user->rating_count,
            'id_verification_status'   => $user->id_verification_status->value ?? $user->id_verification_status,
            'created_at'               => $user->created_at->toDateString(),
        ];
    }

    /** PATCH /api/me — update name / phone. */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $request->user()->update($validated);

        return response()->json(['message' => 'Profile updated.']);
    }

    /** POST /api/me/password — change password. */
    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password updated.']);
    }
}
