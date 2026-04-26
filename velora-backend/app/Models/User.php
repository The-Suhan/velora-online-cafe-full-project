<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function resolvedFeedbacks()
    {
        return $this->hasMany(Feedback::class, 'done_by');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function generateAuthToken(string $name = 'auth-token'): \Laravel\Sanctum\NewAccessToken
    {
        $this->tokens()->delete();

        return $this->createToken($name);
    }
}