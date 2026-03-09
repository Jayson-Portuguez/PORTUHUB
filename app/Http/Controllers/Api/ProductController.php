<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
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

    public function index(): JsonResponse
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return response()->json($products->map(fn ($p) => [
            'id' => $p->id,
            'category' => $p->category,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'imageUrls' => $p->image_urls ?? [],
            'stock' => (int) $p->stock,
            'createdAt' => $p->created_at?->toIso8601String(),
        ]));
    }

    public function new(): JsonResponse
    {
        $products = Product::orderBy('created_at', 'desc')->limit(8)->get();
        return response()->json($products->map(fn ($p) => [
            'id' => $p->id,
            'category' => $p->category,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'imageUrls' => $p->image_urls ?? [],
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
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imageUrls' => 'nullable|array',
            'imageUrls.*' => 'string',
        ]);
        $product = new Product;
        $product->id = Str::uuid()->toString();
        $product->name = $validated['name'];
        $product->category = $validated['category'];
        $product->description = $validated['description'] ?? '';
        $product->price = $validated['price'];
        $product->stock = $validated['stock'];
        $product->image_urls = $validated['imageUrls'] ?? ['/placeholder.png'];
        $product->save();
        return response()->json([
            'id' => $product->id,
            'category' => $product->category,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $product->image_urls ?? [],
            'stock' => (int) $product->stock,
            'createdAt' => $product->created_at?->toIso8601String(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json([
            'id' => $product->id,
            'category' => $product->category,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $product->image_urls ?? [],
            'stock' => (int) $product->stock,
            'createdAt' => $product->created_at?->toIso8601String(),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imageUrls' => 'nullable|array',
            'imageUrls.*' => 'string',
        ]);
        $product->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_urls' => $validated['imageUrls'] ?? $product->image_urls,
        ]);
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imageUrls' => $product->image_urls ?? [],
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
        $product->delete();
        return response()->json(['success' => true]);
    }
}
