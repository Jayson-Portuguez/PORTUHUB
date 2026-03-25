<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'id',
        'name',
        'category',
        'description',
        'price',
        'stock',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** @return list<string> */
    public function orderedImagePaths(): array
    {
        $paths = $this->images()->pluck('path')->all();

        return $paths === [] ? ['/placeholder.svg'] : array_values($paths);
    }

    /**
     * Persist up to 6 image payloads on product_images.path (data URLs from upload API,
     * or legacy /uploads/... strings — no new files are written to disk here).
     */
    public function replaceImages(array $urls): void
    {
        $this->images()->delete();
        $urls = array_values(array_filter($urls, fn ($u) => is_string($u) && $u !== ''));
        if ($urls === []) {
            $urls = ['/placeholder.svg'];
        }
        foreach (array_slice($urls, 0, 6) as $i => $url) {
            ProductImage::query()->create([
                'product_id' => $this->id,
                'path' => self::normalizeStoredImagePath($url),
                'sort_order' => $i,
            ]);
        }
    }

    public static function normalizeStoredImagePath(string $u): string
    {
        $u = trim($u);
        if ($u === '') {
            return '/placeholder.svg';
        }
        if (str_starts_with($u, 'data:')) {
            return $u;
        }
        $u = str_replace('\\', '/', $u);
        $u = str_replace('/public/uploads/', '/uploads/', $u);
        $u = str_replace('public/uploads/', '/uploads/', $u);
        if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
            $path = parse_url($u, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : $u;
        }

        return str_starts_with($u, '/') ? $u : '/'.$u;
    }
}
