<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'score',
        'description',
    ];

    protected $casts = [
        'score'      => 'decimal:1',
        'created_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ── Model Events ──────────────────────────────────────────
    // Rating oluşturulunca / silinince ürünün avg_rating'ini güncelle.
    //
    // NOTE: these only fire for single-model operations. Mass deletes
    // (Rating::where(...)->delete()) bypass them, so those call sites must call
    // Product::syncAvgRating() themselves.

    protected static function booted(): void
    {
        $sync = fn (Rating $rating) => Product::syncAvgRating($rating->product_id);

        static::created($sync);
        static::updated($sync);
        static::deleted($sync);
    }
}
