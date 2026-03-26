<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    private const PLACEHOLDER_DATA_URI = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22400%22%20height%3D%22400%22%20viewBox%3D%220%200%20400%20400%22%3E%3Crect%20fill%3D%22%23e5e5e5%22%20width%3D%22400%22%20height%3D%22400%22%2F%3E%3Ctext%20fill%3D%22%23737373%22%20font-family%3D%22system-ui%2Csans-serif%22%20font-size%3D%2218%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3ENo%20image%3C%2Ftext%3E%3C%2Fsvg%3E';

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

        return $paths === [] ? [self::PLACEHOLDER_DATA_URI] : array_values($paths);
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
            $urls = [self::PLACEHOLDER_DATA_URI];
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
            return self::PLACEHOLDER_DATA_URI;
        }
        if (str_starts_with($u, 'data:')) {
            return $u;
        }
        if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
            return $u;
        }

        return self::PLACEHOLDER_DATA_URI;
    }
}
