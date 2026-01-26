# reCAPTCHA Implementation Guide

## Overview

This document describes the implementation of Google reCAPTCHA v3 for protecting submission forms in the Manhua platform.

## What is reCAPTCHA v3?

reCAPTCHA v3 is an invisible CAPTCHA that runs in the background and returns a score (0.0 to 1.0) indicating the likelihood that the user is human. Unlike v2, it doesn't require user interaction (no checkbox).

## Protected Endpoints

The following endpoints are protected with reCAPTCHA validation:

1. **POST /api/v1/register** - User registration
2. **POST /api/v1/login** - User login
3. **POST /api/v1/comics/{comic}/rate** - Submit comic rating

## Configuration

### 1. Get reCAPTCHA Keys

1. Visit https://www.google.com/recaptcha/admin
2. Register your site with reCAPTCHA v3
3. Get your **Site Key** (public) and **Secret Key** (private)

### 2. Backend Configuration (Laravel)

Add to `.env`:

```env
# Google reCAPTCHA v3 Secret Key
RECAPTCHA_SECRET_KEY=your_secret_key_here
RECAPTCHA_MIN_SCORE=0.5
```

The configuration is loaded from `config/services.php`:

```php
'recaptcha' => [
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
],
```

### 3. Frontend Configuration (Next.js)

Add to `.env.local`:

```env
# Google reCAPTCHA v3 Site Key (Public)
NEXT_PUBLIC_RECAPTCHA_SITE_KEY=your_site_key_here
```

## Implementation Details

### Backend (Laravel)

#### Middleware: `VerifyRecaptcha`

Location: `app/Http/Middleware/VerifyRecaptcha.php`

The middleware:
1. Extracts `recaptcha_token` from the request
2. Verifies the token with Google's API
3. Checks the score against the minimum threshold (default: 0.5)
4. Logs verification attempts
5. Returns 422 error if verification fails

#### Route Protection

Routes are protected by applying the `recaptcha` middleware:

```php
Route::post('/register', [AuthController::class, 'register'])->middleware('recaptcha');
Route::post('/login', [AuthController::class, 'login'])->middleware('recaptcha');
Route::post('/comics/{comic}/rate', [ComicController::class, 'rate'])->middleware('recaptcha');
```

### Frontend (Next.js)

#### Hook: `useRecaptcha`

Location: `manhua-nextjs/lib/hooks/useRecaptcha.ts`

The hook:
1. Loads the reCAPTCHA script dynamically
2. Provides `executeRecaptcha(action)` function to get tokens
3. Returns `isLoaded` status

#### Usage in Forms

**Registration Form** (`app/[locale]/register/page.tsx`):
```typescript
const { executeRecaptcha, isLoaded } = useRecaptcha();

const handleSubmit = async (e) => {
  const recaptchaToken = await executeRecaptcha('register');
  await register(name, email, password, password_confirmation, recaptchaToken);
};
```

**Login Form** (`app/[locale]/login/page.tsx`):
```typescript
const { executeRecaptcha, isLoaded } = useRecaptcha();

const handleSubmit = async (e) => {
  const recaptchaToken = await executeRecaptcha('login');
  await login(email, password, recaptchaToken);
};
```

**Rating Submission** (`components/comic/ComicInfo.tsx`):
```typescript
const { executeRecaptcha } = useRecaptcha();

const handleRatingChange = async (rating) => {
  const recaptchaToken = await executeRecaptcha('rate_comic');
  await rateComic(comic.id, { rating, recaptcha_token: recaptchaToken });
};
```

## Testing

### 1. Test with Valid Keys

1. Register your site at https://www.google.com/recaptcha/admin
2. Add the keys to `.env` and `.env.local`
3. Test registration, login, and rating submission
4. Check Laravel logs for verification results

### 2. Test Without Keys (Development)

If `RECAPTCHA_SECRET_KEY` is not set, the middleware will skip validation and log a warning.

### 3. Monitor Logs

Check `storage/logs/laravel.log` for:
- Successful verifications
- Failed verifications
- Low scores
- Missing tokens

## Score Interpretation

reCAPTCHA v3 returns a score from 0.0 to 1.0:

- **1.0**: Very likely a human
- **0.5**: Neutral (default threshold)
- **0.0**: Very likely a bot

You can adjust the minimum score in `.env`:

```env
RECAPTCHA_MIN_SCORE=0.7  # More strict
RECAPTCHA_MIN_SCORE=0.3  # More lenient
```

## Troubleshooting

### Issue: "reCAPTCHA token is required"

**Cause**: Frontend is not sending the token.

**Solution**: Check that `useRecaptcha` hook is properly integrated and `executeRecaptcha()` is called before form submission.

### Issue: "reCAPTCHA verification failed"

**Possible causes**:
1. Invalid secret key
2. Token expired (tokens are valid for 2 minutes)
3. Token already used
4. Score below threshold

**Solution**: Check Laravel logs for specific error codes.

### Issue: reCAPTCHA badge not showing

**Cause**: Site key not configured or script not loaded.

**Solution**: Verify `NEXT_PUBLIC_RECAPTCHA_SITE_KEY` is set in `.env.local`.

## Files Modified

### Backend (Laravel)
- `app/Http/Middleware/VerifyRecaptcha.php` - Middleware for verification
- `bootstrap/app.php` - Middleware registration
- `config/services.php` - reCAPTCHA configuration
- `routes/api.php` - Applied middleware to routes
- `.env` - Added secret key

### Frontend (Next.js)
- `lib/hooks/useRecaptcha.ts` - reCAPTCHA hook
- `lib/api/auth.ts` - Added recaptcha_token to interfaces
- `lib/api/user.ts` - Added recaptcha_token to rating
- `lib/contexts/AuthContext.tsx` - Updated login/register signatures
- `app/[locale]/register/page.tsx` - Integrated reCAPTCHA
- `app/[locale]/login/page.tsx` - Integrated reCAPTCHA
- `components/comic/ComicInfo.tsx` - Integrated reCAPTCHA for ratings
- `.env.local` - Added site key

## References

- [Google reCAPTCHA Documentation](https://developers.google.com/recaptcha/docs/v3)
- [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)

