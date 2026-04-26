<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class DailyStat extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'stat_date',
        'order_count',
        'pending_orders',
        'completed_orders',
        'cancelled_orders',
        'total_revenue',
        'active_users',
        'avg_order_value',
    ];

    protected $casts = [
        'stat_date'         => 'date',
        'order_count'       => 'integer',
        'pending_orders'    => 'integer',
        'completed_orders'  => 'integer',
        'cancelled_orders'  => 'integer',
        'total_revenue'     => 'decimal:2',
        'active_users'      => 'integer',
        'avg_order_value'   => 'decimal:2',
        'created_at'        => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Bugünün istatistik kaydını orders tablosundan hesaplayıp upsert eder.
     * Cron job / scheduled command ile günlük çağrılır.
     */
    public static function recalculateForDate(Carbon $date): static
    {
        // Tüm siparişleri çek (cancelled dahil — her status ayrı sayılacak)
        $allOrders = Order::whereDate('created_at', $date)->get();

        $pendingOrders   = $allOrders->where('status', 'pending')->count();
        $completedOrders = $allOrders->where('status', 'delivered')->count();
        $cancelledOrders = $allOrders->where('status', 'cancelled')->count();

        // Gelir hesabına sadece tamamlananlar dahil
        $revenueOrders = $allOrders->where('status', 'delivered');
        $orderCount    = $revenueOrders->count();
        $totalRevenue  = $revenueOrders->sum('total_price');
        $activeUsers   = $allOrders->pluck('user_id')->unique()->count();
        $avgOrderValue = $orderCount > 0 ? round($totalRevenue / $orderCount, 2) : 0;

        return static::updateOrCreate(
            ['stat_date' => $date->toDateString()],
            [
                'order_count'      => $allOrders->count(),
                'pending_orders'   => $pendingOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_revenue'    => $totalRevenue,
                'active_users'     => $activeUsers,
                'avg_order_value'  => $avgOrderValue,
            ]
        );
    }
}