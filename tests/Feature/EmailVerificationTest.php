<?php

namespace Tests\Feature;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration sends verification code
     */
    public function test_registration_sends_verification_code(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'recaptcha_token' => 'test_token',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful. Please check your email for verification code.',
                'data' => [
                    'email' => 'test@example.com',
                    'requires_verification' => true,
                ],
            ]);

        // Assert user was created but not verified
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        // Assert verification code was created
        $this->assertDatabaseHas('email_verifications', [
            'email' => 'test@example.com',
            'verified' => false,
        ]);

        // Assert email was queued (not sent immediately because it implements ShouldQueue)
        Mail::assertQueued(\App\Mail\VerificationCodeMail::class);
    }

    /**
     * Test email verification with valid code
     */
    public function test_email_verification_with_valid_code(): void
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        // Create verification code
        $verification = EmailVerification::createForEmail($user->email);

        $response = $this->postJson('/api/v1/verify-email', [
            'email' => $user->email,
            'code' => $verification->code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email verified successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'email_verified_at'],
                    'access_token',
                    'refresh_token',
                    'expires_in',
                ],
            ]);

        // Assert user is now verified
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // Assert verification record is marked as verified
        $this->assertDatabaseHas('email_verifications', [
            'email' => $user->email,
            'code' => $verification->code,
            'verified' => true,
        ]);
    }

    /**
     * Test email verification with invalid code
     */
    public function test_email_verification_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        EmailVerification::createForEmail($user->email);

        $response = $this->postJson('/api/v1/verify-email', [
            'email' => $user->email,
            'code' => '999999', // Invalid code
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);

        // Assert user is still not verified
        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Test email verification with expired code
     */
    public function test_email_verification_with_expired_code(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        // Create verification code that's already expired
        $verification = EmailVerification::create([
            'email' => $user->email,
            'code' => '123456',
            'expires_at' => now()->subMinutes(10), // Expired 10 minutes ago
            'verified' => false,
        ]);

        $response = $this->postJson('/api/v1/verify-email', [
            'email' => $user->email,
            'code' => $verification->code,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid or expired verification code.',
            ]);
    }

    /**
     * Test resend verification code
     */
    public function test_resend_verification_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/resend-verification', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Verification code sent successfully.',
            ]);

        // Assert new verification code was created
        $this->assertDatabaseHas('email_verifications', [
            'email' => $user->email,
            'verified' => false,
        ]);

        // Assert email was queued (not sent immediately because it implements ShouldQueue)
        Mail::assertQueued(\App\Mail\VerificationCodeMail::class);
    }

    /**
     * Test login requires email verification
     */
    public function test_login_requires_email_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
            'recaptcha_token' => 'test_token',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Email not verified. Please verify your email first.',
                'requires_verification' => true,
            ]);
    }

    /**
     * Test login succeeds with verified email
     */
    public function test_login_succeeds_with_verified_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
            'recaptcha_token' => 'test_token',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                    'refresh_token',
                ],
            ]);
    }
}
