<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────
    // GET /api/admin/orders?search=&status=&page=1&per_page=8

    public function index(Request $request): JsonResponse
    {
        // The list only renders a quantity total, so aggregate it in SQL instead
        // of hydrating every order item and its product.
        $query = Order::with('user:id,name,email')
            ->withSum('orderItems as items_count', 'quantity');

        // Search by customer name or email or order id
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                // "#ORD-000320" → extract numeric part
                $numeric = ltrim(preg_replace('/[^0-9]/', '', $search), '0') ?: null;

                if ($numeric) {
                    $q->where('id', $numeric);
                }

                $q->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            });
        }

        // Status filter
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Date range filter
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $perPage = (int) $request->query('per_page', 8);

        $orders = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($o) => $this->formatOrder($o));

        return response()->json($orders);
    }

    // ── STATS ─────────────────────────────────────────────────
    // GET /api/admin/orders/stats

    public function stats(): JsonResponse
    {
        // Totals, week-over-week and the status breakdown in a single pass.
        $agg = Order::selectRaw(
            'COUNT(*) as total,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_week,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as preparing,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ready,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled',
            [
                now()->startOfWeek(), now()->endOfWeek(),
                now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek(),
                'pending', 'preparing', 'ready', 'delivered', 'cancelled',
            ]
        )->first();

        $thisWeek = (int) $agg->this_week;
        $lastWeek = (int) $agg->last_week;

        $growth = $lastWeek === 0
            ? ($thisWeek > 0 ? 100 : 0)
            : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);

        return response()->json([
            'total' => (int) $agg->total,
            'growth' => $growth,
            'pending' => (int) $agg->pending,
            'preparing' => (int) $agg->preparing,
            'ready' => (int) $agg->ready,
            'delivered' => (int) $agg->delivered,
            'cancelled' => (int) $agg->cancelled,
        ]);
    }

    // ── SHOW ──────────────────────────────────────────────────
    // GET /api/admin/orders/{id}
    // Used by the "View" modal — returns full order detail with items & product images.

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'user:id,name,email',
            'orderItems.product:id,name,image_url,price',
        ]);

        return response()->json($this->formatOrder($order, detail: true));
    }

    // ── UPDATE STATUS ─────────────────────────────────────────
    // PATCH /api/admin/orders/{id}/status
    // Body: { "status": "preparing" }
    // Used by the "Edit" modal — change order status.
    //
    // Allowed transitions (guard against random jumps):
    //   pending    → preparing | cancelled
    //   preparing  → ready     | cancelled
    //   ready      → delivered | cancelled
    //   delivered  → (terminal — no change allowed)
    //   cancelled  → (terminal — no change allowed)

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
            'note' => 'nullable|string|max:1000',
        ]);

        $allowed = $this->allowedTransitions($order->status);

        if (! in_array($request->status, $allowed)) {
            return response()->json([
                'message' => "Cannot change status from \"{$order->status}\" to \"{$request->status}\".",
                'allowed' => $allowed,
            ], 422);
        }

        $data = ['status' => $request->status];

        if ($request->filled('note')) {
            $data['note'] = $request->note;
        }

        $order->update($data);

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'note' => $order->note,
            'message' => "Order status updated to \"{$order->status}\".",
        ]);
    }

    // ── UPDATE NOTE ───────────────────────────────────────────
    // PATCH /api/admin/orders/{id}/note
    // Body: { "note": "..." }
    // Allows updating the note field independently without touching status.

    public function updateNote(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $order->update(['note' => $request->note]);

        return response()->json([
            'id' => $order->id,
            'note' => $order->note,
            'message' => 'Order note updated.',
        ]);
    }

    // ── CANCEL ────────────────────────────────────────────────
    // PATCH /api/admin/orders/{id}/cancel
    // Used by the "Cancel" modal (trash icon in UI).
    // Cannot cancel an already delivered or cancelled order.

    public function cancel(Order $order): JsonResponse
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'message' => "Order is already \"{$order->status}\" and cannot be cancelled.",
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'message' => 'Order has been cancelled.',
        ]);
    }

    // ── DESTROY ───────────────────────────────────────────────
    // DELETE /api/admin/orders/{id}
    // Used by the "Delete" modal.
    // Only delivered or cancelled orders can be hard-deleted (keep history clean).

    public function destroy(Order $order): JsonResponse
    {
        if (! in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'message' => 'Only delivered or cancelled orders can be deleted.',
            ], 422);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    // ── EXPORT ────────────────────────────────────────────────
    // GET /api/admin/orders/export?status=&from=&to=
    // Returns all matching orders as JSON array (frontend converts to CSV/Excel).

    public function export(Request $request): JsonResponse
    {
        // Count items in SQL — this used to hydrate every order_item row of every
        // exported order just to call ->count() on the collection.
        $query = Order::with('user:id,name,email')
            ->withCount('orderItems');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => [
                'order_id' => '#ORD-'.str_pad($o->id, 6, '0', STR_PAD_LEFT),
                'customer' => $o->user?->name,
                'email' => $o->user?->email,
                'status' => $o->status,
                'total_price' => (float) $o->total_price,
                'items' => (int) $o->order_items_count,
                'note' => $o->note,
                'created_at' => $o->created_at?->format('M d, Y H:i'),
            ]);

        return response()->json(['data' => $orders]);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────

    /**
     * Returns the statuses an order can transition TO from the given $current status.
     */
    private function allowedTransitions(string $current): array
    {
        return match ($current) {
            'pending' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['delivered', 'cancelled'],
            default => [],   // delivered / cancelled are terminal
        };
    }

    /**
     * Total quantity across the order's items.
     *
     * Prefers the `items_count` aggregate added by withSum() on the list query,
     * then an already-loaded relation, and only falls back to a query as a last
     * resort so this can never silently become an N+1.
     */
    private function itemsCount(Order $o): int
    {
        if (array_key_exists('items_count', $o->getAttributes())) {
            return (int) $o->getAttributes()['items_count'];
        }

        return (int) ($o->relationLoaded('orderItems')
            ? $o->orderItems->sum('quantity')
            : $o->orderItems()->sum('quantity'));
    }

    private function formatOrder(Order $o, bool $detail = false): array
    {
        $base = [
            'id' => $o->id,
            'order_number' => '#ORD-'.str_pad($o->id, 6, '0', STR_PAD_LEFT),
            'status' => $o->status,
            'allowed_next' => $this->allowedTransitions($o->status),
            'total_price' => (float) $o->total_price,
            'note' => $o->note,
            'items_count' => $this->itemsCount($o),
            'customer' => $o->user ? [
                'id' => $o->user->id,
                'name' => $o->user->name,
                'email' => $o->user->email,
            ] : null,
            'delivery_type' => $o->delivery_type,
            'address' => $o->address,
            'phone' => $o->phone,
            'created_at' => $o->created_at?->format('M d, Y'),
            'created_at_time' => $o->created_at?->format('H:i A'),
            'updated_at' => $o->updated_at?->format('M d, Y H:i'),
        ];

        // Detail view — "View" modal
        if ($detail) {
            $base['items'] = $o->orderItems->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'image_url' => $item->product->image_url,
                ] : null,
            ])->values();
        }

        return $base;
    }
}
