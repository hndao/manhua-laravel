<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

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

        // Create user (not verified yet)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => null, // Not verified yet
        ]);

        // Create verification code
        $verification = EmailVerification::createForEmail($user->email);

        // Send verification email
        Mail::to($user->email)->send(new VerificationCodeMail($verification->code, $user->name));

        return response()->json([
            'message' => 'Registration successful. Please check your email for verification code.',
            'data' => [
                'email' => $user->email,
                'requires_verification' => true,
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

        // Check if email is verified (only for non-social users)
        if (!$user->provider && !$user->email_verified_at) {
            return response()->json([
                'message' => 'Email not verified. Please verify your email first.',
                'requires_verification' => true,
                'email' => $user->email,
            ], 403);
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
                    'email_verified_at' => $user->email_verified_at,
                    'avatar' => $user->avatar,
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

    /**
     * Verify email with code
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
        ]);

        // Verify the code
        $isValid = EmailVerification::verify($validated['email'], $validated['code']);

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        // Update user's email_verified_at
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        // Create tokens for the user
        $accessToken = $user->createToken('access-token', ['*'], now()->addDays(7))->plainTextToken;
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 7 * 24 * 60 * 60,
            ],
        ]);
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        // Create new verification code
        $verification = EmailVerification::createForEmail($user->email);

        // Send verification email
        Mail::to($user->email)->send(new VerificationCodeMail($verification->code, $user->name));

        return response()->json([
            'message' => 'Verification code sent successfully.',
        ]);
    }

    /**
     * Redirect to social provider
     */
    public function redirectToProvider(string $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook', 'twitter'])) {
            return response()->json([
                'message' => 'Invalid social provider.',
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            return response()->json([
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to redirect to provider: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback(string $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook', 'twitter'])) {
            return response()->json([
                'message' => 'Invalid social provider.',
            ], 400);
        }

        try {
            // Get user from provider
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = User::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if (!$user) {
                // Check if email already exists
                $existingUser = User::where('email', $socialUser->getEmail())->first();

                if ($existingUser) {
                    // Link social account to existing user
                    $existingUser->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                        'email' => $socialUser->getEmail(),
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'email_verified_at' => null, // Will be verified via email code
                    ]);
                }

                // Create verification code and send email
                $verification = EmailVerification::createForEmail($user->email);
                Mail::to($user->email)->send(new VerificationCodeMail($verification->code, $user->name));

                return response()->json([
                    'message' => 'Social login successful. Please check your email for verification code.',
                    'data' => [
                        'email' => $user->email,
                        'requires_verification' => true,
                        'provider' => $provider,
                    ],
                ], 201);
            }

            // Check if email is verified
            if (!$user->email_verified_at) {
                // Resend verification code
                $verification = EmailVerification::createForEmail($user->email);
                Mail::to($user->email)->send(new VerificationCodeMail($verification->code, $user->name));

                return response()->json([
                    'message' => 'Email not verified. Verification code sent to your email.',
                    'data' => [
                        'email' => $user->email,
                        'requires_verification' => true,
                        'provider' => $provider,
                    ],
                ], 403);
            }

            // User exists and is verified, create tokens
            $accessToken = $user->createToken('access-token', ['*'], now()->addDays(7))->plainTextToken;
            $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'message' => 'Social login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'avatar' => $user->avatar,
                        'provider' => $user->provider,
                        'created_at' => $user->created_at,
                    ],
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_in' => 7 * 24 * 60 * 60, // 7 days in seconds
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Social login failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

