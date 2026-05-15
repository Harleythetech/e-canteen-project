<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

// Mass-assignable fields — only these columns can be set via create() or fill()
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
// These fields are never included in JSON/array output for security
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Tell Laravel how to cast raw database values to PHP types.
     * - email_verified_at becomes a Carbon datetime object
     * - password is automatically hashed when set
     * - is_active becomes a true/false boolean
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * A user can have many orders.
     * Usage: $user->orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Returns true if this user is a student.
     * Used in views and middleware to check access level.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Returns true if this user is a staff member.
     * Used in views and middleware to check access level.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Returns true if this user is an admin.
     * Used in views and middleware to check access level.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Generates a 1–2 letter avatar initials string from the user's name.
     * Example: "Juan dela Cruz" → "JD"
     * Used in the navigation avatar component.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
