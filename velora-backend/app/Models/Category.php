<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(string $locale = 'en'): ?CategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

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
}
