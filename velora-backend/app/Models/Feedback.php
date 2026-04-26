<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'message',
        'is_done',
        'done_by',
    ];

    protected $casts = [
        'is_done'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Geçerli feedback türleri
    const TYPES = ['complaint', 'request', 'question'];

    // ── Relations ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Admin tarafından tamamlandı olarak işaretle.
     */
    public function markAsDone(User $admin): void
    {
        $this->update([
            'is_done'    => true,
            'done_by'    => $admin->id,
            'updated_at' => now(),
        ]);
    }
}
