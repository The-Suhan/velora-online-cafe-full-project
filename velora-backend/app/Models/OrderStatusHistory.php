<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bir siparişin durum geçişlerinin kaydı. Order::booted() tarafından yazılır.
 */
class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';

    // Sadece created_at var, updated_at yok.
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
