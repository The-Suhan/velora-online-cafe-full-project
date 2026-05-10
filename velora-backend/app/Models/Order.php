<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'delivery_type',
        'address',
        'phone',
        'note',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Geçerli sipariş durumları
    const STATUSES = ['pending', 'preparing', 'ready', 'delivered', 'cancelled'];

    const DELIVERY_TYPES = ['pickup', 'delivery'];

    // ── Relations ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'preparing']);
    }

    /**
     * Sipariş kalemlerinden total_price'ı yeniden hesaplar.
     */
    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['total_price' => $total]);
    }
}
