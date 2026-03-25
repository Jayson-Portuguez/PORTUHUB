<?php

namespace App\Console\Commands;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportProductsFromJson extends Command
{
    protected $signature = 'products:import-json {file=data/products.json : Path relative to project base, or absolute}';

    protected $description = 'Import products from a JSON file into the database (one-time migration helper)';

    public function handle(): int
    {
        $file = $this->argument('file');
        $path = $this->resolvePath($file);
        if (! is_readable($path)) {
            $this->error("File not readable: {$path}");

            return self::FAILURE;
        }
        $decoded = json_decode(file_get_contents($path), true);
        if (! is_array($decoded)) {
            $this->error('Invalid JSON: expected a top-level array of products.');

            return self::FAILURE;
        }

        $imported = 0;
        foreach ($decoded as $i => $row) {
            if (! is_array($row)) {
                $this->warn("Skipping index {$i}: not an object.");

                continue;
            }
            if (empty($row['name']) || ! is_string($row['name'])) {
                $this->warn("Skipping index {$i}: missing name.");

                continue;
            }
            $id = isset($row['id']) && is_string($row['id']) && $row['id'] !== ''
                ? $row['id']
                : (string) Str::uuid();

            $imageUrls = $row['imageUrls'] ?? null;
            if (! is_array($imageUrls) || $imageUrls === []) {
                $imageUrls = ['/placeholder.svg'];
            }
            $imageUrls = array_values(array_filter($imageUrls, fn ($u) => is_string($u) && $u !== ''));
            if ($imageUrls === []) {
                $imageUrls = ['/placeholder.svg'];
            }

            $data = [
                'name' => $row['name'],
                'category' => (isset($row['category']) && is_string($row['category']) && $row['category'] !== '')
                    ? $row['category']
                    : 'Others',
                'description' => isset($row['description']) && is_string($row['description'])
                    ? $row['description']
                    : '',
                'price' => isset($row['price']) ? (float) $row['price'] : 0.0,
                'stock' => isset($row['stock']) ? (int) $row['stock'] : 0,
            ];

            $existing = Product::query()->find($id);
            if ($existing) {
                $existing->update($data);
                $existing->replaceImages($imageUrls);
            } else {
                $attrs = array_merge(['id' => $id], $data);
                if (isset($row['createdAt']) && is_string($row['createdAt'])) {
                    try {
                        $attrs['created_at'] = Carbon::parse($row['createdAt']);
                    } catch (\Throwable) {
                        // keep default
                    }
                }
                $product = Product::query()->create($attrs);
                $product->replaceImages($imageUrls);
            }
            $imported++;
        }

        $this->info("Imported/updated {$imported} product(s).");

        return self::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        if ($file !== '' && (str_starts_with($file, DIRECTORY_SEPARATOR) || preg_match('#^[a-zA-Z]:[\\\\/]#', $file))) {
            return $file;
        }

        return base_path($file);
    }
}
