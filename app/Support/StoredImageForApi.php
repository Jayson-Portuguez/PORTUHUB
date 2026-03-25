<?php

namespace App\Support;

/**
 * Turns stored image values (data URLs, legacy /uploads paths, etc.) into values
 * the SPA can use without resolving fragile file paths on the client.
 */
final class StoredImageForApi
{
    public static function resolve(?string $stored): ?string
    {
        if ($stored === null) {
            return null;
        }
        $stored = trim($stored);
        if ($stored === '') {
            return self::placeholderData();
        }
        if (str_starts_with($stored, 'data:')) {
            return $stored;
        }
        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            $path = parse_url($stored, PHP_URL_PATH);
            if (is_string($path) && ($path === '/placeholder.svg' || str_starts_with($path, '/uploads/'))) {
                return self::resolveLocalPath($path);
            }

            return $stored;
        }

        return self::resolveLocalPath($stored);
    }

    private static function resolveLocalPath(string $path): string
    {
        $path = self::normalizePublicPath($path);
        if ($path === '/placeholder.svg') {
            return self::placeholderData();
        }
        if (str_starts_with($path, '/')) {
            $full = public_path(ltrim($path, '/'));
            $data = self::fileToDataUrl($full);

            return $data ?? self::placeholderData();
        }

        return self::placeholderData();
    }

    private static function normalizePublicPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = str_replace(['/public/uploads/', 'public/uploads/'], '/uploads/', $path);

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    private static function fileToDataUrl(string $absolute): ?string
    {
        if (! is_file($absolute) || ! is_readable($absolute)) {
            return null;
        }
        try {
            $bin = file_get_contents($absolute);
            if ($bin === false) {
                return null;
            }
            $mime = mime_content_type($absolute) ?: 'application/octet-stream';
            if ($mime === 'image/jfif') {
                $mime = 'image/jpeg';
            }

            return 'data:'.$mime.';base64,'.base64_encode($bin);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function placeholderData(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        $p = public_path('placeholder.svg');
        $data = self::fileToDataUrl($p);

        return $cached = $data ?? 'data:image/svg+xml;charset=UTF-8,'.rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400"><rect fill="#e5e5e5" width="400" height="400"/><text fill="#737373" font-size="18" x="50%" y="50%" text-anchor="middle">No image</text></svg>'
        );
    }
}
