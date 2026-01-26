<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get reCAPTCHA token from request
        $recaptchaToken = $request->input('recaptcha_token');

        // Skip validation if reCAPTCHA is not configured
        $secretKey = config('services.recaptcha.secret_key');
        if (!$secretKey) {
            Log::warning('reCAPTCHA secret key not configured. Skipping validation.');
            return $next($request);
        }

        // If no token provided, reject the request
        if (!$recaptchaToken) {
            return response()->json([
                'success' => false,
                'message' => 'reCAPTCHA token is required.',
            ], 422);
        }

        // Verify reCAPTCHA token with Google
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaToken,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            // Check if verification was successful
            if (!$result['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ], 422);
            }

            // For reCAPTCHA v3, check the score (optional)
            // Score ranges from 0.0 (likely bot) to 1.0 (likely human)
            $score = $result['score'] ?? null;
            $minScore = config('services.recaptcha.min_score', 0.5);

            if ($score !== null && $score < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $score,
                    'min_score' => $minScore,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ], 422);
            }

            // Log successful verification
            Log::info('reCAPTCHA verification successful', [
                'score' => $score,
                'action' => $result['action'] ?? null,
                'ip' => $request->ip(),
            ]);

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'reCAPTCHA verification error. Please try again.',
            ], 500);
        }

        return $next($request);
    }
}
