<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Canlı sipariş takibi için polling uç noktaları.
 *
 * Bu iki endpoint saniyede birkaç kez çağrılabilir, o yüzden bilinçli olarak
 * minimal: tek indeksli SELECT, eager load yok, sayfalama yok.
 *
 * Cursor tasarımı: istemci hiçbir zaman kendi saatini kullanmaz. Yanıttaki
 * `server_time` sorgudan ÖNCE alınır ve istemci onu bir sonraki isteğe `since`
 * olarak geri gönderir; böylece saat kayması ve gidiş-dönüş boşluğu sorun olmaz.
 * Format mikrosaniyeli (`.u`) — Postgres timestamp mikrosaniye tutar, saniye
 * hassasiyetli bir cursor aynı saniyedeki ikinci güncellemeyi kaybederdi.
 */
class OrderUpdatesController extends Controller
{
    /** Cursor ve yanıtlarda kullanılan zaman formatı. */
    private const TS = 'Y-m-d H:i:s.u';

    /** Tek bir yanıtta dönebilecek en fazla satır. */
    private const MAX_ROWS = 50;

    // ── CUSTOMER ──────────────────────────────────────────────
    // GET /api/orders/updates?since=<cursor>

    public function customer(Request $request): JsonResponse
    {
        $request->validate(['since' => 'nullable|date']);

        $now = now();
        $userId = $request->user()->id;

        $query = Order::where('user_id', $userId);

        if ($since = $request->query('since')) {
            $query->where('updated_at', '>', Carbon::parse($since));
        } else {
            // İlk çağrı: istemcinin önbelleğini tohumlamak için sadece canlı siparişler.
            $query->active();
        }

        $orders = $query
            ->orderByDesc('updated_at')
            ->limit(self::MAX_ROWS)
            ->get(['id', 'status', 'updated_at']);

        return response()->json([
            'server_time' => $now->format(self::TS),
            'active_count' => Order::where('user_id', $userId)->active()->count(),
            'orders' => $orders->map(fn (Order $o) => [
                'id' => $o->id,
                'order_no' => '#ORD-'.str_pad((string) $o->id, 9, '0', STR_PAD_LEFT),
                'status' => $o->status,
                'updated_at' => $o->updated_at->format(self::TS),
            ])->values(),
        ]);
    }

    // ── ADMIN ─────────────────────────────────────────────────
    // GET /api/admin/orders/updates?since=<cursor>

    public function admin(Request $request): JsonResponse
    {
        $request->validate(['since' => 'nullable|date']);

        $now = now();

        // Postgres'e özel FILTER yerine stats() ile aynı taşınabilir biçim —
        // feature testleri sqlite üzerinde koşuyor.
        $counts = Order::selectRaw(
            'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as preparing,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ready',
            ['pending', 'preparing', 'ready']
        )->first();

        $newOrders = 0;
        $changed = [];

        if ($since = $request->query('since')) {
            $cursor = Carbon::parse($since);

            $rows = Order::where('updated_at', '>', $cursor)
                ->orderByDesc('updated_at')
                ->limit(self::MAX_ROWS)
                ->get(['id', 'status', 'created_at', 'updated_at']);

            $changed = $rows->map(fn (Order $o) => [
                'id' => $o->id,
                'status' => $o->status,
                'is_new' => $o->created_at->gt($cursor),
            ])->values();

            $newOrders = $changed->where('is_new', true)->count();
        }

        return response()->json([
            'server_time' => $now->format(self::TS),
            'new_orders' => $newOrders,
            'changed' => $changed,
            'counts' => [
                'pending' => (int) $counts->pending,
                'preparing' => (int) $counts->preparing,
                'ready' => (int) $counts->ready,
            ],
        ]);
    }
}
