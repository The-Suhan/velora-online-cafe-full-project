<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────
    // GET /api/admin/categories?search=&page=1&per_page=6

    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products');

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $perPage = (int) $request->query('per_page', 6);

        $categories = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($c) => $this->formatCategory($c));

        return response()->json($categories);
    }

    // ── STATS ─────────────────────────────────────────────────
    // GET /api/admin/categories/stats

    public function stats(): JsonResponse
    {
        $total = Category::count();

        $thisWeek = Category::whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();

        $lastWeek = Category::whereBetween('created_at', [
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

    // ── SHOW ──────────────────────────────────────────────────
    // GET /api/admin/categories/{id}

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return response()->json($this->formatCategory($category, detail: true));
    }

    // ── STORE ─────────────────────────────────────────────────
    // POST /api/admin/categories
    // Body: multipart/form-data { name, description?, is_active?, image? }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->uploadImage($request->file('image'));
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'image_url' => $imageUrl,
        ]);

        $category->loadCount('products');

        return response()->json($this->formatCategory($category), 201);
    }

    // ── UPDATE ────────────────────────────────────────────────
    // POST /api/admin/categories/{id}?_method=PUT  (multipart icin)

    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:100|unique:categories,name,'.$category->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = array_filter($request->only(['name', 'description']), fn ($v) => $v !== null);

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image')) {
            if ($category->image_url) {
                $this->deleteImage($category->image_url);
            }
            $data['image_url'] = $this->uploadImage($request->file('image'));
        }

        $category->update($data);
        $category->loadCount('products');

        return response()->json($this->formatCategory($category));
    }

    // ── DESTROY ───────────────────────────────────────────────
    // DELETE /api/admin/categories/{id}

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with products. Remove or reassign products first.',
            ], 422);
        }

        if ($category->image_url) {
            $this->deleteImage($category->image_url);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    // ── TOGGLE ACTIVE ─────────────────────────────────────────
    // PATCH /api/admin/categories/{id}/toggle

    public function toggle(Category $category): JsonResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json([
            'id' => $category->id,
            'is_active' => $category->is_active,
            'message' => $category->is_active ? 'Category activated.' : 'Category deactivated.',
        ]);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────

    private function uploadImage(UploadedFile $file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('categories', $filename, 'public');

        return Storage::url($path);
    }

    private function deleteImage(string $url): void
    {
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        Storage::disk('public')->delete($path);
    }

    private function formatCategory(Category $c, bool $detail = false): array
    {
        $base = [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'image_url' => $c->image_url,
            'is_active' => $c->is_active,
            'products_count' => $c->products_count ?? 0,
            'created_at' => $c->created_at?->format('M d, Y'),
        ];

        if ($detail) {
            $base['products'] = $c->products()
                ->select('id', 'name', 'price', 'image_url', 'is_active')
                ->take(5)
                ->get();
        }

        return $base;
    }
}
