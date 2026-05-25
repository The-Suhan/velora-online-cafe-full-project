<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // CATEGORIES
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/categories
     * Aktif ana kategorileri + alt kategorileri döndürür.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::with([
            'translations',
            'children' => fn ($q) => $q->where('is_active', true)->with('translations'),
        ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn ($c) => $this->formatCategory($c, withChildren: true));

        return response()->json($categories);
    }

    /**
     * GET /api/categories/{category}/products
     * Bir kategoriye ait aktif ürünleri döndürür.
     * Query params: search, sort (price_asc|price_desc|rating|newest), per_page
     */
    public function categoryProducts(Request $request, Category $category): JsonResponse
    {
        // Alt kategorilerin ürünlerini de dahil et
        $categoryIds = collect([$category->id])
            ->merge($category->children()->pluck('id'));

        $query = Product::with(['category:id,name', 'translations'])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $sort = $request->query('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('avg_rating'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = (int) $request->query('per_page', 12);

        $products = $query
            ->paginate($perPage)
            ->through(fn ($p) => $this->formatProduct($p));

        return response()->json($products);
    }

    // ═══════════════════════════════════════════════════════════
    // PRODUCTS
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/products
     * Tüm aktif ürünler (search + sort destekli).
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'translations'])
            ->where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $sort = $request->query('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('avg_rating'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = (int) $request->query('per_page', 12);

        $products = $query
            ->paginate($perPage)
            ->through(fn ($p) => $this->formatProduct($p));

        return response()->json($products);
    }

    /**
     * GET /api/products/{product}
     * Ürün detayı + kullanıcının mevcut rating'i.
     */
    public function showProduct(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->load(['category:id,name', 'translations']);

        $userRating = Rating::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        $data = $this->formatProduct($product, detail: true);
        $data['user_rating'] = $userRating ? [
            'score' => (float) $userRating->score,
            'description' => $userRating->description,
        ] : null;

        return response()->json($data);
    }

    // ═══════════════════════════════════════════════════════════
    // RATINGS
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/products/{product}/rate
     * Ürüne puan ver veya mevcut puanı güncelle.
     * Body: { score: 4.5, description: "..." }
     */
    public function rateProduct(Request $request, Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $request->validate([
            'score' => 'required|numeric|min:0.5|max:5.0',
            'description' => 'nullable|string|max:1000',
        ]);

        // 0.5 adımlarına yuvarlama (örn: 3.3 → 3.5)
        $score = round((float) $request->score * 2) / 2;

        $rating = Rating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ],
            [
                'score' => $score,
                'description' => $request->description,
            ]
        );

        // avg_rating'i yeniden hesapla
        $avg = Rating::where('product_id', $product->id)->avg('score');
        $product->update(['avg_rating' => round($avg, 2)]);

        return response()->json([
            'message' => 'Rating saved successfully.',
            'score' => (float) $rating->score,
            'avg_rating' => (float) $product->fresh()->avg_rating,
        ]);
    }

    /**
     * DELETE /api/products/{product}/rate
     * Kullanıcının kendi rating'ini siler.
     */
    public function deleteRating(Product $product): JsonResponse
    {
        $deleted = Rating::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'No rating found to delete.'], 404);
        }

        // avg_rating'i yeniden hesapla
        $avg = Rating::where('product_id', $product->id)->avg('score') ?? 0;
        $product->update(['avg_rating' => round($avg, 2)]);

        return response()->json(['message' => 'Rating deleted successfully.']);
    }

    /**
     * GET /api/products/{product}/ratings
     * Ürünün tüm yorumlarını listele (sayfalı).
     */
    public function productRatings(Request $request, Product $product): JsonResponse
    {
        $ratings = Rating::with('user:id,name')
            ->where('product_id', $product->id)
            ->whereNotNull('description')
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 10))
            ->through(fn ($r) => [
                'id' => $r->id,
                'score' => (float) $r->score,
                'description' => $r->description,
                'user_name' => $r->user?->name,
                'created_at' => $r->created_at->format('M d, Y'),
            ]);

        return response()->json($ratings);
    }

    // ═══════════════════════════════════════════════════════════
    // ORDERS
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/orders
     * Yeni sipariş oluştur.
     * Body: {
     *   items: [{ product_id, quantity }],
     *   note: "...",
     *   delivery_type: "pickup"|"delivery",
     *   address: "...",
     *   phone: "..."
     * }
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'note' => 'nullable|string|max:500',
            'delivery_type' => 'nullable|in:pickup,delivery',
            'address' => 'required_if:delivery_type,delivery|nullable|string|max:500',
            'phone' => 'required_if:delivery_type,delivery|nullable|string|max:20',
        ]);

        // Ürünleri tek sorguda çek
        $productIds = collect($request->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Pasif/silinmiş ürün kontrolü
        foreach ($request->items as $item) {
            if (! isset($products[$item['product_id']])) {
                return response()->json([
                    'message' => "Product #{$item['product_id']} is not available.",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];
                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'note' => $request->note,
                'total_price' => $totalPrice,
                'delivery_type' => $request->input('delivery_type', 'pickup'),
                'address' => $request->address,
                'phone' => $request->phone,
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => 'Order could not be placed. Please try again.'], 500);
        }

        $order->load('items.product:id,name,image_url');

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => $this->formatOrder($order),
        ], 201);
    }

    /**
     * GET /api/orders
     * Kullanıcının kendi siparişleri (sayfalı, en yeni önce).
     */
    public function myOrders(Request $request): JsonResponse
    {
        $orders = Order::with('items.product:id,name,image_url')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 10))
            ->through(fn ($o) => $this->formatOrder($o));

        return response()->json($orders);
    }

    /**
     * GET /api/orders/{order}
     * Sipariş detayı (sadece kendi siparişi).
     */
    public function showOrder(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->load('items.product:id,name,image_url,price');

        return response()->json($this->formatOrder($order, detail: true));
    }

    /**
     * PATCH /api/orders/{order}/cancel
     * Sadece pending durumundaki siparişi iptal edebilir.
     */
    public function cancelOrder(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'status' => $order->status,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    private function formatCategory(Category $c, bool $withChildren = false): array
    {
        $locale = app()->getLocale(); // 'en', 'ru', 'tm'
        $translation = $c->translations->firstWhere('locale', $locale);

        $data = [
            'id' => $c->id,
            'name' => $translation?->name ?? $c->name,
            'description' => $translation?->description ?? $c->description,
            'image_url' => $c->image_url,
            'is_active' => $c->is_active,
        ];

        if ($withChildren) {
            $data['children'] = $c->children
                ->map(fn ($child) => $this->formatCategory($child))
                ->values();
        }

        return $data;
    }

    private function formatProduct(Product $p, bool $detail = false): array
    {
        $locale = app()->getLocale();
        $translation = $p->translations->firstWhere('locale', $locale);

        $data = [
            'id' => $p->id,
            'name' => $translation?->name ?? $p->name,
            'description' => $translation?->description ?? $p->description,
            'price' => (float) $p->price,
            'image_url' => $p->image_url,
            'avg_rating' => (float) $p->avg_rating,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name' => $p->category->name,
            ] : null,
        ];

        if ($detail) {
            $data['ratings_count'] = $p->ratings()->count();
        }

        return $data;
    }

    private function formatOrder(Order $o, bool $detail = false): array
    {
        $data = [
            'id' => $o->id,
            'order_no' => '#ORD-'.str_pad($o->id, 9, '0', STR_PAD_LEFT),
            'status' => $o->status,
            'total_price' => (float) $o->total_price,
            'delivery_type' => $o->delivery_type,
            'note' => $o->note,
            'created_at' => $o->created_at->format('M d, Y H:i'),
            'items' => $o->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'image_url' => $item->product?->image_url,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ]),
        ];

        if ($detail) {
            $data['address'] = $o->address;
            $data['phone'] = $o->phone;
        }

        return $data;
    }
}
