# Authentication

## Overview

Authentication is handled by **Laravel Fortify** — a backend authentication library that provides login, registration, password reset, email verification, and two-factor authentication (2FA).

---

## Registration

New users register at `/register`. The `CreateNewUser` action (`app/Actions/Fortify/CreateNewUser.php`) handles validation and creation:

- Name, email, and password are required
- Password must meet the default rules: min 8 chars, mixed case, letters, numbers, symbols
- New users are always created with `role = student` and `is_active = true`
- After registration, users are redirected to `/menu`

---

## Login

Login is at `/login`. Rate limited to **5 attempts per minute** per email+IP combination.

After successful login, users are redirected based on their role:
- `admin` → `/admin`
- `staff` → `/staff`
- `student` → `/menu`

This is configured in `FortifyServiceProvider::configureRedirects()`.

---

## Two-Factor Authentication (2FA)

2FA uses TOTP (Time-based One-Time Passwords) compatible with apps like Google Authenticator, Authy, or 1Password.

### Enabling 2FA
1. Go to Settings → Security
2. Click **Enable Two-Factor Authentication**
3. Scan the QR code with your authenticator app
4. Enter a code to confirm
5. Save your recovery codes in a safe place

### Logging In with 2FA
After entering your password, you'll be prompted for a 6-digit code from your authenticator app. Rate limited to **5 attempts per minute** per session.

### Recovery Codes
If you lose access to your authenticator app, use one of your recovery codes. Each code can only be used once.

---

## Password Reset

1. Go to `/forgot-password`
2. Enter your email address
3. Check your email for a reset link
4. Click the link and enter a new password

---

## Email Verification

New accounts require email verification before accessing the app. A verification email is sent on registration. The `/email/verify` page allows resending the verification email.

---

## Role-Based Access Control

The `EnsureUserHasRole` middleware (`app/Http/Middleware/EnsureUserHasRole.php`) is registered as the `role` alias and used in route groups:

```php
// Staff and admin can access
Route::middleware(['auth', 'role:staff,admin'])->group(...)

// Admin only
Route::middleware(['auth', 'role:admin'])->group(...)
```

**What it checks:**
1. User is authenticated
2. User's `role` is in the allowed list
3. User's `is_active` is `true`

If `is_active` is false, the user is immediately logged out and redirected to login with the message: *"Your account has been deactivated."*

---

## Deactivated Accounts

Admins can deactivate any user account (except their own) from the User Management page. Deactivated users:
- Cannot log in
- Are logged out immediately on their next request if already logged in
- Can be reactivated by an admin at any time

---

## Logout

Logout invalidates the session and regenerates the CSRF token. Users are redirected to `/` (the welcome page).

The `Logout` action is in `app/Livewire/Actions/Logout.php` and is called from the user menu dropdown in all layouts.
