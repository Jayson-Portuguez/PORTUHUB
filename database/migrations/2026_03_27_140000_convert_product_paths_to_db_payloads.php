<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PLACEHOLDER_DATA_URI = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22400%22%20height%3D%22400%22%20viewBox%3D%220%200%20400%20400%22%3E%3Crect%20fill%3D%22%23e5e5e5%22%20width%3D%22400%22%20height%3D%22400%22%2F%3E%3Ctext%20fill%3D%22%23737373%22%20font-family%3D%22system-ui%2Csans-serif%22%20font-size%3D%2218%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3ENo%20image%3C%2Ftext%3E%3C%2Fsvg%3E';

    public function up(): void
    {
        $rows = DB::table('product_images')->select('id', 'path')->get();

        foreach ($rows as $row) {
            $path = is_string($row->path) ? trim($row->path) : '';
            if ($path === '' || str_starts_with($path, 'data:') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }

            $normalized = str_replace('\\', '/', $path);
            $normalized = str_replace('/public/uploads/', '/uploads/', $normalized);
            $normalized = str_replace('public/uploads/', '/uploads/', $normalized);
            $normalized = str_starts_with($normalized, '/') ? $normalized : '/'.$normalized;
            $absolute = public_path(ltrim($normalized, '/'));

            $payload = self::PLACEHOLDER_DATA_URI;
            if (is_file($absolute) && is_readable($absolute)) {
                $bin = @file_get_contents($absolute);
                if ($bin !== false) {
                    $mime = @mime_content_type($absolute) ?: 'application/octet-stream';
                    if ($mime === 'image/jfif') {
                        $mime = 'image/jpeg';
                    }
                    $payload = 'data:'.$mime.';base64,'.base64_encode($bin);
                }
            }

            DB::table('product_images')->where('id', $row->id)->update([
                'path' => $payload,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
