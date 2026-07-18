<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

/**
 * Verifies a Firebase ID token from the Flutter app and returns a Sanctum token.
 *
 * Flow:
 *   1. Flutter signs the user in with Google via Firebase Auth.
 *   2. Flutter calls getIdToken() and POSTs it here.
 *   3. We verify the token with the Firebase Admin SDK.
 *   4. We find-or-create the user and issue a Sanctum personal access token.
 */
class GoogleAuthController extends Controller
{
    public function __construct(private readonly FirebaseAuth $auth) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firebase_token' => 'required|string',
            'device_name'    => 'required|string',
            'role'           => 'sometimes|string|in:renter,lister',
        ]);

        try {
            $token = $this->auth->verifyIdToken($data['firebase_token']);
        } catch (FailedToVerifyToken $e) {
            return response()->json(['message' => 'Invalid or expired Google token.'], 401);
        }

        $claims = $token->claims();

        $googleId = $claims->get('sub');              // Firebase UID == Google sub
        $email    = $claims->get('email');
        $name     = $claims->get('name', '');
        $avatar   = $claims->get('picture', null);

        // Find by firebase/google UID first, then fall back to email
        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        $isNew = false;

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleId, 'avatar_url' => $avatar]);
            }
        } else {
            $isNew = true;
            $role  = $data['role'] ?? 'renter';

            $user = User::create([
                'google_id'      => $googleId,
                'name'           => $name,
                'email'          => $email,
                'avatar_url'     => $avatar,
                'password'       => bcrypt(Str::random(32)),
                'role'           => $role,
                'tos_accepted_at'=> now(),
                'referral_code'  => $this->uniqueReferralCode(),
            ]);
        }

        if ($user->isSuspended()) {
            return response()->json(['message' => 'This account has been suspended.'], 403);
        }

        return response()->json([
            'user'    => $this->userPayload($user),
            'token'   => $user->createToken($data['device_name'])->plainTextToken,
            'is_new'  => $isNew,
        ], $isNew ? 201 : 200);
    }

    private function uniqueReferralCode(): string
    {
        do { $code = strtoupper(Str::random(8)); }
        while (User::where('referral_code', $code)->exists());
        return $code;
    }

    private function userPayload(User $user): array
    {
        return [
            'id'                     => $user->id,
            'name'                   => $user->name,
            'email'                  => $user->email,
            'phone'                  => $user->phone,
            'role'                   => $user->role->value,
            'avatar_url'             => $user->avatar_url,
            'id_verification_status' => $user->id_verification_status->value,
            'referral_code'          => $user->referral_code,
        ];
    }
}
