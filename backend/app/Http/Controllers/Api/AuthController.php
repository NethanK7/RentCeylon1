<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Token-based auth for the Flutter mobile app (Sanctum personal access tokens).
 */
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in([Role::Renter->value, Role::Lister->value])],
            'accept_tos' => 'accepted',
            'device_name' => 'required|string',
        ], ['accept_tos.accepted' => 'You must accept the Terms of Service.']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'tos_accepted_at' => now(),
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $user->createToken($data['device_name'])->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        if ($user->isSuspended()) {
            throw ValidationException::withMessages(['email' => ['This account is suspended.']]);
        }

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $user->createToken($data['device_name'])->plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role->value,
            'id_verification_status' => $user->id_verification_status->value,
            'referral_code' => $user->referral_code,
        ];
    }
}
