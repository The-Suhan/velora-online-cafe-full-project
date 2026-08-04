<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AdminController extends Controller
{
    // ── DASHBOARD ─────────────────────────────────────────────
    // GET /api/admin/dashboard

    public function dashboard(): JsonResponse
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $prevWeekStart = now()->subWeek()->startOfWeek();
        $prevWeekEnd = now()->subWeek()->endOfWeek();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        // One aggregate pass over users instead of three separate COUNTs.
        $userAgg = User::selectRaw(
            'COUNT(*) as total,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_week',
            [$weekStart, $weekEnd, $prevWeekStart, $prevWeekEnd]
        )->first();

        // One aggregate pass over orders instead of seven separate COUNT/SUMs.
        $orderAgg = Order::selectRaw(
            'COUNT(*) as total,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_week,'
            .' SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,'
            ." SUM(CASE WHEN status = ? AND created_at BETWEEN ? AND ? THEN total_price ELSE 0 END) as week_revenue,"
            ." SUM(CASE WHEN status = ? AND created_at BETWEEN ? AND ? THEN total_price ELSE 0 END) as last_week_revenue,"
            ." SUM(CASE WHEN status = ? AND created_at BETWEEN ? AND ? THEN total_price ELSE 0 END) as year_revenue",
            [
                $weekStart, $weekEnd,
                $prevWeekStart, $prevWeekEnd,
                'pending',
                'delivered', $weekStart, $weekEnd,
                'delivered', $prevWeekStart, $prevWeekEnd,
                'delivered', $yearStart, $yearEnd,
            ]
        )->first();

        $totalUsers = (int) $userAgg->total;
        $thisWeekUsers = (int) $userAgg->this_week;
        $lastWeekUsers = (int) $userAgg->last_week;

        $totalOrders = (int) $orderAgg->total;
        $thisWeekOrders = (int) $orderAgg->this_week;
        $lastWeekOrders = (int) $orderAgg->last_week;
        $pendingOrders = (int) $orderAgg->pending;
        $thisWeekRevenue = (float) $orderAgg->week_revenue;
        $lastWeekRevenue = (float) $orderAgg->last_week_revenue;
        $yearlyRevenue = (float) $orderAgg->year_revenue;

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $unreadFeedbacks = Feedback::where('is_done', false)->count();

        // Top rated products — the `ratings` relation was eager loaded but never
        // used by the dashboard, pulling every rating row for each product.
        $topProducts = Product::where('avg_rating', '>', 0)
            ->orderByDesc('avg_rating')
            ->take(4)
            ->get();

        // Recent delivered orders — only the first item's product is rendered,
        // so select just the columns the payload needs.
        $recentOrders = Order::with([
            'user:id,name',
            'items:id,order_id,product_id',
            'items.product:id,name,image_url',
        ])
            ->where('status', 'delivered')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_no' => '#ORD-'.str_pad($o->id, 9, '0', STR_PAD_LEFT),
                'user_name' => $o->user?->name,
                'product_name' => $o->items->first()?->product?->name ?? '-',
                'product_img' => $o->items->first()?->product?->image_url,
                'total_price' => $o->total_price,
                'created_at' => $o->created_at,
            ]);

        // Categories with product count
        $categories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get();

        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'users_growth' => $this->calcGrowth($lastWeekUsers, $thisWeekUsers),
                'orders_growth' => $this->calcGrowth($lastWeekOrders, $thisWeekOrders),
                'pending_orders' => $pendingOrders,
                'unread_feedbacks' => $unreadFeedbacks,
                'weekly_revenue' => (float) $thisWeekRevenue,
                'yearly_revenue' => (float) $yearlyRevenue,
                'revenue_growth' => $this->calcGrowth((int) $lastWeekRevenue, (int) $thisWeekRevenue),
            ],
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
            'categories' => $categories,
        ]);
    }

    // ── ORDER CHART ───────────────────────────────────────────
    // GET /api/admin/orders/chart?period=weekly
    // GET /api/admin/orders/chart?period=custom&start_date=2024-08-01&end_date=2024-08-31

    public function ordersChart(Request $request): JsonResponse
    {
        $period = $request->query('period', 'weekly');

        $data = match ($period) {
            'daily' => $this->chartDaily(),
            'monthly' => $this->chartMonthly(),
            'yearly' => $this->chartYearly(),
            'custom' => $this->chartCustom(
                $request->query('start_date', now()->subDays(30)->toDateString()),
                $request->query('end_date', now()->toDateString())
            ),
            default => $this->chartWeekly(),
        };

        return response()->json($data);
    }

    // ── CHART HELPERS ─────────────────────────────────────────

    private function chartWeekly(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $results = Order::selectRaw('EXTRACT(DOW FROM created_at) as dow, COUNT(*) as count')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupByRaw('EXTRACT(DOW FROM created_at)')
            ->pluck('count', 'dow')
            ->toArray();

        return [
            'labels' => $days,
            'data' => [
                $results[1] ?? 0, // Mon
                $results[2] ?? 0,
                $results[3] ?? 0,
                $results[4] ?? 0,
                $results[5] ?? 0,
                $results[6] ?? 0,
                $results[0] ?? 0, // Sun
            ],
        ];
    }

    private function chartDaily(): array
    {
        // Single grouped query — this used to fire one COUNT per hour (24 queries).
        $results = Order::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', today())
            ->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->pluck('count', 'hour')
            ->toArray();

        $labels = [];
        $data = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $data[] = (int) ($results[$h] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function chartMonthly(): array
    {
        $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $start = now()->startOfMonth();
        $end = $start->copy()->addWeeks(4)->subSecond();

        // Single grouped query over the whole 4-week window, bucketed in PHP.
        // Week boundaries fall on midnight, so day-level grouping is exact.
        $byDay = Order::selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $data = array_fill(0, 4, 0);
        foreach ($byDay as $day => $count) {
            $offset = (int) floor($start->diffInDays(Carbon::parse($day)) / 7);
            if ($offset >= 0 && $offset < 4) {
                $data[$offset] += (int) $count;
            }
        }

        return ['labels' => $weeks, 'data' => $data];
    }

    private function chartYearly(): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $results = Order::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('count', 'month')
            ->toArray();

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[] = $results[$m] ?? 0;
        }

        return ['labels' => $months, 'data' => $data];
    }

    // ── NEW: Custom date range ────────────────────────────────
    // Returns one data point per day between $from and $to.
    // Labels: "01 Aug", "02 Aug", ...

    private function chartCustom(string $from, string $to): array
    {
        // Guard: max 366 days to prevent huge queries
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        if ($start->diffInDays($end) > 366) {
            $end = $start->copy()->addDays(366)->endOfDay();
        }

        $results = Order::selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Build a complete date series (fill gaps with 0)
        $labels = [];
        $data = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();          // "2024-08-01"
            $labels[] = $cursor->format('d M');            // "01 Aug"
            $data[] = (int) ($results[$key] ?? 0);
            $cursor->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // ── GROWTH CALC ───────────────────────────────────────────

    private function calcGrowth(int $last, int $current): float
    {
        if ($last === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $last) / $last) * 100, 1);
    }

    // ── USERS ─────────────────────────────────────────────────
    // GET /api/admin/users

    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('role', 'ilike', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            if ($role === 'suspended') {
                $query->where('is_verified', false);
            } else {
                $query->where('role', $role);
            }
        }

        $perPage = (int) $request->query('per_page', 10);

        $users = $query->orderByDesc('created_at')->paginate($perPage);

        // Resolve last-activity for the whole page in one query instead of one
        // token lookup per user inside formatUser().
        $lastActivity = $this->lastActivityMap($users->pluck('id')->all());

        $users->through(fn ($u) => $this->formatUser($u, $lastActivity[$u->id] ?? null));

        return response()->json($users);
    }

    /**
     * Latest token usage per user id, keyed by user id.
     *
     * @param  array<int>  $userIds
     * @return array<int, \Illuminate\Support\Carbon>
     */
    private function lastActivityMap(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return PersonalAccessToken::query()
            ->where('tokenable_type', (new User)->getMorphClass())
            ->whereIn('tokenable_id', $userIds)
            ->whereNotNull('last_used_at')
            ->groupBy('tokenable_id')
            ->selectRaw('tokenable_id, MAX(last_used_at) as last_used_at')
            ->pluck('last_used_at', 'tokenable_id')
            ->map(fn ($ts) => Carbon::parse($ts))
            ->all();
    }

    // ── USER STATS ────────────────────────────────────────────
    // GET /api/admin/users/stats

    public function userStats(): JsonResponse
    {
        // Total + month-over-month in one pass instead of three COUNTs.
        $agg = User::selectRaw(
            'COUNT(*) as total,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_month,'
            .' SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_month',
            [
                now()->startOfMonth(), now()->endOfMonth(),
                now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth(),
            ]
        )->first();

        return response()->json([
            'total' => (int) $agg->total,
            'growth' => $this->calcGrowth((int) $agg->last_month, (int) $agg->this_month),
        ]);
    }

    // ── USER SHOW ─────────────────────────────────────────────
    // GET /api/admin/users/{id}

    public function showUser(User $user): JsonResponse
    {
        $lastUsed = $this->lastActivityMap([$user->id])[$user->id] ?? null;

        return response()->json($this->formatUser($user, $lastUsed, detail: true));
    }

    // ── USER DELETE ───────────────────────────────────────────
    // DELETE /api/admin/users/{id}

    public function destroyUser(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    // ── PRIVATE HELPER ────────────────────────────────────────

    /**
     * @param  \Illuminate\Support\Carbon|null  $lastUsed  Pre-resolved to avoid a per-user token query.
     */
    private function formatUser(User $u, $lastUsed = null, bool $detail = false): array
    {
        $base = [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'is_verified' => $u->is_verified,
            'status' => $u->is_verified ? 'Active' : 'Suspended',
            'last_activity' => $lastUsed ? $lastUsed->diffForHumans() : 'Never',
            'joined' => $u->created_at->format('M Y'),
            'created_at' => $u->created_at,
        ];

        if ($detail) {
            $base['order_count'] = $u->orders()->count();
        }

        return $base;
    }
}
