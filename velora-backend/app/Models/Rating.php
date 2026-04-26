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
    // Rating oluşturulunca / silinince ürünün avg_rating'ini güncelle

    protected static function booted(): void
    {
        static::created(function (Rating $rating) {
            $rating->product->recalculateAvgRating();
        });

        static::updated(function (Rating $rating) {
            $rating->product->recalculateAvgRating();
        });

        static::deleted(function (Rating $rating) {
            $rating->product->recalculateAvgRating();
        });
    }
}
