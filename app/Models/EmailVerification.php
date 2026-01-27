<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'verified' => 'boolean',
    ];

    /**
     * Generate a random 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification code for an email
     */
    public static function createForEmail(string $email): self
    {
        // Delete any existing unverified codes for this email
        self::where('email', $email)
            ->where('verified', false)
            ->delete();

        $code = self::generateCode();
        $expiresAt = now()->addMinutes(5);

        Log::info('Creating verification code', [
            'email' => $email,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Verify a code for an email
     */
    public static function verify(string $email, string $code): bool
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->where('verified', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            Log::warning('Verification failed', [
                'email' => $email,
                'code' => $code,
                'reason' => 'Code not found or expired',
            ]);
            return false;
        }

        $verification->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        Log::info('Email verified successfully', [
            'email' => $email,
        ]);

        return true;
    }

    /**
     * Check if an email is verified
     */
    public static function isVerified(string $email): bool
    {
        return self::where('email', $email)
            ->where('verified', true)
            ->exists();
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
