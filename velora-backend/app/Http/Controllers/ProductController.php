<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'translations']);

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('is_active') && $request->query('is_active') !== '') {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->query('per_page', 8);

        $products = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($p) => $this->formatProduct($p));

        return response()->json($products);
    }

    public function stats(): JsonResponse
    {
        $total = Product::count();

        $thisWeek = Product::whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();

        $lastWeek = Product::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek(),
        ])->count();

        $growth = $lastWeek === 0
            ? ($thisWeek > 0 ? 100 : 0)
            : round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);

        return response()->json([
            'total' => $total,
            'growth' => $growth,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category:id,name', 'translations']);

        return response()->json($this->formatProduct($product, detail: true));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:200|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'translations' => 'nullable|array',
            'translations.en' => 'nullable|array',
            'translations.en.name' => 'required_with:translations.en|string|max:200',
            'translations.en.description' => 'nullable|string',
            'translations.ru' => 'nullable|array',
            'translations.ru.name' => 'required_with:translations.ru|string|max:200',
            'translations.ru.description' => 'nullable|string',
            'translations.tm' => 'nullable|array',
            'translations.tm.name' => 'required_with:translations.tm|string|max:200',
            'translations.tm.description' => 'nullable|string',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->uploadImage($request->file('image'));
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'is_active' => $request->boolean('is_active', true),
            'image_url' => $imageUrl,
        ]);

        foreach (['en', 'ru', 'tm'] as $locale) {
            if ($data = $request->input("translations.$locale")) {
                $product->translations()->create([
                    'locale' => $locale,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                ]);
            }
        }

        $product->load(['category:id,name', 'translations']);

        return response()->json($this->formatProduct($product), 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:200|unique:products,name,'.$product->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'translations' => 'nullable|array',
            'translations.en' => 'nullable|array',
            'translations.en.name' => 'required_with:translations.en|string|max:200',
            'translations.en.description' => 'nullable|string',
            'translations.ru' => 'nullable|array',
            'translations.ru.name' => 'required_with:translations.ru|string|max:200',
            'translations.ru.description' => 'nullable|string',
            'translations.tm' => 'nullable|array',
            'translations.tm.name' => 'required_with:translations.tm|string|max:200',
            'translations.tm.description' => 'nullable|string',
        ]);

        $data = array_filter(
            $request->only(['category_id', 'name', 'description', 'price']),
            fn ($v) => $v !== null
        );

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                $this->deleteImage($product->image_url);
            }
            $data['image_url'] = $this->uploadImage($request->file('image'));
        }

        $product->update($data);

        foreach (['en', 'ru', 'tm'] as $locale) {
            if ($data = $request->input("translations.$locale")) {
                $product->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $data['name'], 'description' => $data['description'] ?? null]
                );
            }
        }

        $product->load(['category:id,name', 'translations']);

        return response()->json($this->formatProduct($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->orderItems()->exists()) {
            return response()->json([
                'message' => 'This product has been used in orders and cannot be deleted.',
            ], 422);
        }

        if ($product->image_url) {
            $this->deleteImage($product->image_url);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function toggle(Product $product): JsonResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'id' => $product->id,
            'is_active' => $product->is_active,
            'message' => $product->is_active
                ? 'Product has been activated.'
                : 'Product has been deactivated.',
        ]);
    }

    public function translations(Product $product): JsonResponse
    {
        return response()->json($product->translations->keyBy('locale'));
    }

    private function uploadImage(UploadedFile $file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('products', $filename, 'public');

        return Storage::url($path);
    }

    private function deleteImage(string $url): void
    {
        $path = ltrim(str_replace('/storage', '', parse_url($url, PHP_URL_PATH)), '/');
        Storage::disk('public')->delete($path);
    }

    private function formatProduct(Product $p, bool $detail = false): array
    {
        $base = [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'image_url' => $p->image_url,
            'is_active' => $p->is_active,
            'avg_rating' => (float) $p->avg_rating,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name' => $p->category->name,
            ] : null,
            'created_at' => $p->created_at?->format('M d, Y'),
            'translations' => $p->translations->keyBy('locale'),
        ];

        if ($detail) {
            $base['ratings_count'] = $p->ratings()->count();
            'order_count' && $base['order_count'] = $p->orderItems()->sum('quantity');
        }

        return $base;
    }
}
