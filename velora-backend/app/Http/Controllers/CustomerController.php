<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /** Products shown per carousel row on the menu home page. */
    private const HOME_PRODUCTS_PER_ROW = 20;

    public function showCategory(Category $category): JsonResponse
    {
        $category->load([
            'translations',
            'children' => fn ($q) => $q->where('is_active', true)->with('translations'),
        ]);

        return response()->json($this->formatCategory($category, withChildren: true));
    }
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
     * GET /api/home
     *
     * The menu home page renders one product carousel per category. It used to
     * build that by calling /categories and then /categories/{id}/products once
     * per category — 1 + N HTTP round trips. This returns the same rows in two
     * queries.
     *
     * Row shape matches the client's existing structure: each category object
     * plus a `products` array, categories with no products omitted.
     */
    public function home(): JsonResponse
    {
        $roots = Category::with([
            'translations',
            'children' => fn ($q) => $q->where('is_active', true)->with('translations'),
        ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        // Prefer subcategories as the carousel rows; fall back to roots when
        // there are none — same rule the page applied client-side.
        $subCategories = $roots->flatMap(fn ($c) => $c->children);
        $targets = $subCategories->isNotEmpty() ? $subCategories : $roots;

        if ($targets->isEmpty()) {
            return response()->json([]);
        }

        // A category row also shows its children's products (matching
        // categoryProducts()), so map each target to the ids it covers.
        $childIds = Category::whereIn('parent_id', $targets->pluck('id'))
            ->get(['id', 'parent_id'])
            ->groupBy('parent_id')
            ->map(fn ($rows) => $rows->pluck('id')->all());

        $idsByTarget = $targets->mapWithKeys(fn ($c) => [
            $c->id => array_merge([$c->id], $childIds[$c->id] ?? []),
        ]);

        $allIds = collect($idsByTarget->values())->flatten()->unique()->values();

        $productsByCategory = Product::with(['category:id,name', 'translations'])
            ->whereIn('category_id', $allIds)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('category_id');

        $rows = $targets->map(function ($category) use ($idsByTarget, $productsByCategory) {
            $products = collect($idsByTarget[$category->id])
                ->flatMap(fn ($id) => $productsByCategory[$id] ?? collect())
                // Re-sort after merging child buckets, then apply the same
                // per_page=20 cap the client used to request.
                ->sortByDesc('created_at')
                ->take(self::HOME_PRODUCTS_PER_ROW)
                ->map(fn ($p) => $this->formatProduct($p))
                ->values();

            return $this->formatCategory($category) + ['products' => $products];
        })
            ->filter(fn ($row) => $row['products']->isNotEmpty())
            ->values();

        return response()->json($rows);
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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('description', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->query('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        if ($request->filled('on_discount')) {
            if (filter_var($request->query('on_discount'), FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull('discount_percent')->where('discount_percent', '>', 0);
            } else {
                $query->where(fn ($q) => $q->whereNull('discount_percent')->orWhere('discount_percent', 0));
            }
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
     * Tüm aktif ürünler (search + sort + filtre destekli).
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'translations'])
            ->where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('description', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->query('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        if ($request->filled('on_discount')) {
            if (filter_var($request->query('on_discount'), FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull('discount_percent')->where('discount_percent', '>', 0);
            } else {
                $query->where(fn ($q) => $q->whereNull('discount_percent')->orWhere('discount_percent', 0));
            }
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
            'score' => 'nullable|numeric|min:0.5|max:5.0',
            'description' => 'nullable|string|max:1000',
        ]);

        // score null ise rating'i sil
        if (is_null($request->score)) {
            Rating::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->delete();

            // Mass delete skips model events, so sync explicitly.
            return response()->json([
                'message' => 'Rating removed.',
                'avg_rating' => Product::syncAvgRating($product->id),
            ]);
        }

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

        // The Rating created/updated event already recomputed avg_rating; just
        // read the stored value back instead of recomputing and refetching.
        return response()->json([
            'message' => 'Rating saved successfully.',
            'score' => (float) $rating->score,
            'avg_rating' => (float) Product::whereKey($product->id)->value('avg_rating'),
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

        // Mass delete skips model events, so sync explicitly.
        Product::syncAvgRating($product->id);

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
            'phone' => 'required|string|max:20',
        ]);

        // Ürünleri tek sorguda çek — only the columns the order needs
        $productIds = collect($request->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get(['id', 'price', 'discount_percent'])
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
                // Charge whatever the product's current discounted price is —
                // the price actually shown to the customer in the menu/cart —
                // so orders always reflect the price paid at checkout time.
                $unitPrice = $product->final_price;
                $subtotal = round($unitPrice * $item['quantity'], 2);
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
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

            // Single bulk insert instead of one INSERT per line item.
            $order->items()->insert(array_map(
                fn (array $item) => $item + ['order_id' => $order->id],
                $orderItems
            ));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => 'Order could not be placed. Please try again.'], 500);
        }

        $order->load('items.product:id,name,image_url', 'items.product.translations');

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
        $orders = Order::with('items.product:id,name,image_url', 'items.product.translations')
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

        $order->load('items.product:id,name,image_url,price', 'items.product.translations', 'statusHistory');

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
        $data = [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'image_url' => $c->image_url,
            'is_active' => $c->is_active,
            'translations' => $c->translations->keyBy('locale'),
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
        $data = [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'discount_percent' => $p->discount_percent,
            'has_discount' => $p->hasDiscount(),
            'final_price' => $p->final_price,
            'image_url' => $p->image_url,
            'avg_rating' => (float) $p->avg_rating,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name' => $p->category->name,
            ] : null,
            'translations' => $p->translations->keyBy('locale'),
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
                'product_translations' => $item->product?->translations?->keyBy('locale'),
                'image_url' => $item->product?->image_url,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ]),
        ];

        $data['address'] = $o->address;
        $data['phone'] = $o->phone;

        if ($detail) {
            // Tracker'ın her adımın altında zaman damgası gösterebilmesi için.
            $data['status_history'] = $o->statusHistory->map(fn ($h) => [
                'to_status' => $h->to_status,
                'at' => $h->created_at?->format('M d, H:i'),
            ])->values();
            $data['updated_at_iso'] = $o->updated_at->format('Y-m-d H:i:s.u');
        }

        return $data;
    }

    public function myFavorites(Request $request): JsonResponse
    {
        $ratings = Rating::with('product.category:id,name')
            ->where('user_id', auth()->id())
            ->orderByDesc('score')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'product_id' => $r->product_id,
                'score' => (float) $r->score,
                'product_name' => $r->product?->name,
                'image_url' => $r->product?->image_url,
                'price' => (float) ($r->product?->price ?? 0),
                'category' => $r->product?->category?->name,
                'rated_at' => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($ratings);
    }

    /**
     * GET /api/me/feedback
     * Kullanıcının kendi feedbackleri.
     */
    public function myFeedback(Request $request): JsonResponse
    {
        $feedbacks = Feedback::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'type' => $f->type,
                'subject' => $f->subject,
                'message' => $f->message,
                'is_done' => $f->is_done,
                'created_at' => $f->created_at?->format('M d, Y'),
            ]);

        return response()->json($feedbacks);
    }

    /**
     * POST /api/me/feedback
     * Yeni feedback gönder.
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:complaint,request,question',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'is_done' => false,
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully.',
            'feedback' => [
                'id' => $feedback->id,
                'type' => $feedback->type,
                'subject' => $feedback->subject,
                'message' => $feedback->message,
                'is_done' => $feedback->is_done,
                'created_at' => $feedback->created_at?->format('M d, Y'),
            ],
        ], 201);
    }
}
