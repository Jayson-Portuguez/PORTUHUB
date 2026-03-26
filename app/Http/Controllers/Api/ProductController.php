<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * @param  list<string>  $urls
     * @return list<string>
     */
    private function toClientImageUrls(array $urls): array
    {
        return array_values(array_filter(array_map(function ($u) {
            if (! is_string($u) || $u === '') {
                return null;
            }
            if (str_starts_with($u, 'data:')) {
                return $u;
            }
            if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
                return $u;
            }

            return Product::normalizeStoredImagePath($u);
        }, $urls)));
    }

    /** @return list<string> */
    private function imagePathsFromProduct(Product $p): array
    {
        return $p->relationLoaded('images')
            ? $p->images->pluck('path')->values()->all()
            : $p->orderedImagePaths();
    }

    private function syncProductImages(Product $product, array $urls): void
    {
        $product->replaceImages($urls);
    }

    private function adminToken(): ?string
    {
        $token = request()->cookie('admin_session');
        if ($token) {
            return $token;
        }
        $header = request()->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    private function isAdmin(): bool
    {
        $token = $this->adminToken();
        if (! $token) {
            return false;
        }

        return \App\Models\AdminSession::where('token', $token)
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function logProductActivity(string $action, string $productId, string $productName, ?array $meta = null): void
    {
        try {
            AdminActivityLog::query()->create([
                'id' => Str::uuid()->toString(),
                'action' => $action,
                'product_id' => $productId,
                'product_name' => $productName,
                'meta' => $meta,
            ]);
        } catch (\Throwable) {
            //
        }
    }

    /** Non-empty category label for API / UI (legacy rows may be null or blank). */
    private function categoryForApi(mixed $value): string
    {
        if (! is_string($value)) {
            return 'Others';
        }
        $v = trim($value);

        return $v !== '' ? $v : 'Others';
    }

    /**
     * @return array<string, mixed>
     */
    private function productToSummary(Product $p): array
    {
        return [
            'id' => $p->id,
            'category' => $this->categoryForApi($p->category),
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'imageUrls' => $this->toClientImageUrls($this->imagePathsFromProduct($p)),
            'stock' => (int) $p->stock,
            'createdAt' => $p->created_at?->toIso8601String(),
        ];
    }

    public function categories(): JsonResponse
    {
        $cats = Product::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json($cats->values()->all());
    }

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with('images')->orderBy('created_at', 'desc');

        $category = $request->query('category');
        if (is_string($category) && $category !== '') {
            $query->where('category', $category);
        }

        if ($request->query('page') !== null && $request->query('page') !== '') {
            $perPage = (int) $request->query('per_page', 10);
            $perPage = max(1, min($perPage, 50));

            return response()->json(
                $query->paginate($perPage)->through(fn (Product $p) => $this->productToSummary($p))
            );
        }

        return response()->json(
            $query->get()->map(fn (Product $p) => $this->productToSummary($p))
        );
    }

    public function new(): JsonResponse
    {
        $products = Product::query()->with('images')->orderBy('created_at', 'desc')->limit(8)->get();

        return response()->json($products->map(fn ($p) => [
            'id' => $p->id,
            'category' => $this->categoryForApi($p->category),
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'imageUrls' => $this->toClientImageUrls($this->imagePathsFromProduct($p)),
            'stock' => (int) $p->stock,
            'createdAt' => $p->created_at?->toIso8601String(),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999999999.99',
            'stock' => 'required|integer|min:0',
            'imageUrls' => 'nullable|array|max:6',
            'imageUrls.*' => 'string|max:3000000',
        ]);
        $product = new Product;
        $product->id = Str::uuid()->toString();
        $product->name = $validated['name'];
        $product->category = $validated['category'];
        $product->description = $validated['description'] ?? '';
        $product->price = $validated['price'];
        $product->stock = $validated['stock'];
        $product->save();
        $this->syncProductImages($product, $validated['imageUrls'] ?? []);
        $product->load('images');
        $this->logProductActivity('product_created', $product->id, $product->name);

        return response()->json([
            'id' => $product->id,
            'category' => $this->categoryForApi($product->category),
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $this->toClientImageUrls($this->imagePathsFromProduct($product)),
            'stock' => (int) $product->stock,
            'createdAt' => $product->created_at?->toIso8601String(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::query()->with('images')->find($id);
        if (! $product) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'category' => $this->categoryForApi($product->category),
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $this->toClientImageUrls($this->imagePathsFromProduct($product)),
            'stock' => (int) $product->stock,
            'createdAt' => $product->created_at?->toIso8601String(),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $product = Product::query()->with('images')->find($id);
        if (! $product) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999999999.99',
            'stock' => 'required|integer|min:0',
            'imageUrls' => 'nullable|array|max:6',
            'imageUrls.*' => 'string|max:3000000',
        ]);
        $product->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'stock' => $validated['stock'],
        ]);
        if (array_key_exists('imageUrls', $validated)) {
            $this->syncProductImages($product, $validated['imageUrls']);
        }
        $product->refresh();
        $product->load('images');
        $this->logProductActivity('product_updated', $product->id, $product->name);

        return response()->json([
            'id' => $product->id,
            'category' => $this->categoryForApi($product->category),
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $this->toClientImageUrls($this->imagePathsFromProduct($product)),
            'stock' => (int) $product->stock,
            'createdAt' => $product->created_at?->toIso8601String(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $name = $product->name;
        $product->delete();
        $this->logProductActivity('product_deleted', $id, $name);

        return response()->json(['success' => true]);
    }
}
