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
        'discount_percent',
        'image_url',
        'is_active',
        'avg_rating',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'is_active' => 'boolean',
        'discount_percent' => 'integer',
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

    // ── Discount ──────────────────────────────────────────────

    public function hasDiscount(): bool
    {
        return $this->discount_percent !== null && $this->discount_percent > 0;
    }

    /**
     * Price the customer actually pays right now — discounted if a
     * discount_percent is set, otherwise the plain price. This is what
     * gets stored on the order item at checkout time.
     */
    public function getFinalPriceAttribute(): float
    {
        if (! $this->hasDiscount()) {
            return (float) $this->price;
        }

        return round((float) $this->price * (100 - $this->discount_percent) / 100, 2);
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Ratings tablosundan avg_rating'i yeniden hesaplar ve kaydeder.
     * Trigger yerine model event ile kullanılabilir.
     */
    public function recalculateAvgRating(): float
    {
        $avg = static::syncAvgRating($this->id);
        $this->avg_rating = $avg;

        return $avg;
    }

    /**
     * Recomputes and persists avg_rating for a product id, returning the new value.
     *
     * Works off the id alone so callers (model events in particular) never have to
     * hydrate the Product just to update one column.
     */
    public static function syncAvgRating(int $productId): float
    {
        $avg = round((float) (Rating::where('product_id', $productId)->avg('score') ?? 0), 2);

        static::whereKey($productId)->update(['avg_rating' => $avg]);

        return $avg;
    }
}
