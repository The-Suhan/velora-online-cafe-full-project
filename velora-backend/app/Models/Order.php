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

    // ── Model events ──────────────────────────────────────────

    /**
     * Durum geçmişini modelin kendisi yazar; böylece hangi controller'dan
     * gelirse gelsin (admin updateStatus/cancel, müşteri cancelOrder) kayıt tutulur.
     */
    protected static function booted(): void
    {
        static::created(function (Order $order) {
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $order->status,
                'changed_by' => $order->user_id,
                'created_at' => $order->created_at ?? now(),
            ]);
        });

        static::updated(function (Order $order) {
            // Not güncellemesi gibi durum değiştirmeyen update'ler kayıt yazmaz.
            if (! $order->wasChanged('status')) {
                return;
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->getOriginal('status'),
                'to_status' => $order->status,
                'changed_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }

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

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
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
