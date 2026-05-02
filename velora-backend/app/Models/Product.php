<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image_url',
        'is_active',
        'avg_rating',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Ratings tablosundan avg_rating'i yeniden hesaplar ve kaydeder.
     * Trigger yerine model event ile kullanılabilir.
     */
    public function recalculateAvgRating(): void
    {
        $avg = $this->ratings()->avg('score') ?? 0;
        $this->update(['avg_rating' => round($avg, 2)]);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
