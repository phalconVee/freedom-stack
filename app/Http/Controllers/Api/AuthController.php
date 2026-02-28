<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Return Vuexy-compatible auth response: accessToken, userData, userAbilityRules.
     */
    private function authResponse(User $user, string $token): JsonResponse
    {
        $userData = [
            'id' => $user->id,
            'fullName' => $user->name,
            'username' => str($user->email)->before('@')->toString(),
            'email' => $user->email,
            'role' => 'user',
        ];

        $userAbilityRules = [
            ['action' => 'read', 'subject' => 'Auth'],
        ];

        return response()->json([
            'accessToken' => $token,
            'userData' => $userData,
            'userAbilityRules' => $userAbilityRules,
        ], 201);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return $this->authResponse($user, $token);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [Str::lower($request->input('email'))])->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('spa')->plainTextToken;

        return $this->authResponse($user, $token);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $userData = [
            'id' => $user->id,
            'fullName' => $user->name,
            'username' => str($user->email)->before('@')->toString(),
            'email' => $user->email,
            'role' => 'user',
        ];

        return response()->json(['userData' => $userData]);
    }
}
