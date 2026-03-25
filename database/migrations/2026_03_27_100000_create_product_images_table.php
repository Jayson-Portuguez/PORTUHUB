<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 100);
            $table->longText('path');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->index(['product_id', 'sort_order']);
        });

        $products = DB::table('products')->select('id', 'image_urls')->get();

        foreach ($products as $row) {
            $urls = [];
            if ($row->image_urls !== null && $row->image_urls !== '') {
                $decoded = json_decode($row->image_urls, true);
                $urls = is_array($decoded) ? array_values($decoded) : [];
            }
            $urls = array_values(array_filter($urls, fn ($u) => is_string($u) && $u !== ''));
            if ($urls === []) {
                $urls = ['/placeholder.svg'];
            }
            foreach (array_slice($urls, 0, 6) as $i => $url) {
                DB::table('product_images')->insert([
                    'product_id' => $row->id,
                    'path' => $this->normalizePathFromLegacy($url),
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_urls');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('image_urls')->nullable();
        });

        $groups = DB::table('product_images')
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('product_id');

        foreach ($groups as $productId => $rows) {
            $urls = $rows->pluck('path')->values()->all();
            DB::table('products')->where('id', $productId)->update([
                'image_urls' => json_encode($urls),
            ]);
        }

        Schema::dropIfExists('product_images');
    }

    private function normalizePathFromLegacy(string $u): string
    {
        $u = str_replace('\\', '/', trim($u));
        if (str_starts_with($u, 'data:')) {
            return $u;
        }
        $u = str_replace('/public/uploads/', '/uploads/', $u);
        $u = str_replace('public/uploads/', '/uploads/', $u);

        if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
            $path = parse_url($u, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : $u;
        }

        return str_starts_with($u, '/') ? $u : '/'.$u;
    }
};
