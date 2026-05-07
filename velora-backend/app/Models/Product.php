<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'price' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'is_active' => 'boolean',
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

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(string $locale = 'en'): ?ProductTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    // Aktif locale için name döndür, fallback: en, sonra orijinal name
    public function getTranslatedName(string $locale = 'en'): string
    {
        return $this->translation($locale)?->name
            ?? $this->translation('en')?->name
            ?? $this->name;
    }

    public function getTranslatedDescription(string $locale = 'en'): ?string
    {
        return $this->translation($locale)?->description
            ?? $this->translation('en')?->description
            ?? $this->description;
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
