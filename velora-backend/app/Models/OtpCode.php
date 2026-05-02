<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpCode extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'otp_codes';

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'is_used'    => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }
}
