<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create access token (expires in 7 days)
        $accessToken = $user->createToken('access-token', ['*'], now()->addDays(7))->plainTextToken;

        // Create refresh token (expires in 30 days)
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 7 * 24 * 60 * 60, // 7 days in seconds
            ],
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all previous tokens
        $user->tokens()->delete();

        // Create access token (expires in 7 days)
        $accessToken = $user->createToken('access-token', ['*'], now()->addDays(7))->plainTextToken;

        // Create refresh token (expires in 30 days)
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 7 * 24 * 60 * 60, // 7 days in seconds
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'created_at' => $request->user()->created_at,
            ],
        ]);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Verify the current token has 'refresh' ability
        if (!$request->user()->currentAccessToken()->can('refresh')) {
            return response()->json([
                'message' => 'Invalid refresh token',
            ], 401);
        }

        // Delete the old refresh token
        $request->user()->currentAccessToken()->delete();

        // Delete all access tokens (optional - for security)
        $user->tokens()->where('name', 'access-token')->delete();

        // Create new access token (expires in 7 days)
        $accessToken = $user->createToken('access-token', ['*'], now()->addDays(7))->plainTextToken;

        // Create new refresh token (expires in 30 days)
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 7 * 24 * 60 * 60, // 7 days in seconds
            ],
        ]);
    }
}

