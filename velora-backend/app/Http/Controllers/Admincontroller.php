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

class AdminController extends Controller
{
    // ── DASHBOARD ─────────────────────────────────────────────
    // GET /api/admin/dashboard

    public function dashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        $thisWeekUsers = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeekUsers = User::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();

        $thisWeekOrders = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeekOrders = Order::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();

        $pendingOrders = Order::where('status', 'pending')->count();
        $unreadFeedbacks = Feedback::where('is_done', false)->count();

        // Top rated products
        $topProducts = Product::with('ratings')
            ->where('avg_rating', '>', 0)
            ->orderByDesc('avg_rating')
            ->take(4)
            ->get();

        // Recent delivered orders
        $recentOrders = Order::with(['user', 'items.product'])
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
        $labels = [];
        $data = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $data[] = Order::whereDate('created_at', today())
                ->whereRaw('EXTRACT(HOUR FROM created_at) = ?', [$h])
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function chartMonthly(): array
    {
        $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $data = [];
        $start = now()->startOfMonth();

        for ($w = 0; $w < 4; $w++) {
            $from = $start->copy()->addWeeks($w);
            $to = $from->copy()->addWeek()->subSecond();
            $data[] = Order::whereBetween('created_at', [$from, $to])->count();
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

        $users = $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($u) => $this->formatUser($u));

        return response()->json($users);
    }

    // ── USER STATS ────────────────────────────────────────────
    // GET /api/admin/users/stats

    public function userStats(): JsonResponse
    {
        $total = User::count();

        $thisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        return response()->json([
            'total' => $total,
            'growth' => $this->calcGrowth($lastMonth, $thisMonth),
        ]);
    }

    // ── USER SHOW ─────────────────────────────────────────────
    // GET /api/admin/users/{id}

    public function showUser(User $user): JsonResponse
    {
        return response()->json($this->formatUser($user, detail: true));
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

    private function formatUser(User $u, bool $detail = false): array
    {
        $lastUsed = $u->tokens()->latest('last_used_at')->value('last_used_at');

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
