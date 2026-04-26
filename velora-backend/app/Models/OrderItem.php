<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'price'      => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * quantity * price'dan subtotal'ı otomatik doldurur.
     */
    public static function createWithSubtotal(array $attributes): static
    {
        $attributes['subtotal'] = $attributes['quantity'] * $attributes['price'];
        return static::create($attributes);
    }
}
