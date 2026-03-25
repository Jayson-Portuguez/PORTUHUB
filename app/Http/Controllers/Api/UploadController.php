<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadController extends Controller
{
    private const MAX_EDGE = 1024;

    private const JPEG_QUALITY = 78;

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

    /**
     * Encode raw bytes as a data URL (fallback when GD cannot decode).
     */
    private function rawDataUrl(string $contents, string $ext, string $mime): string
    {
        if ($ext === 'jfif' || $mime === 'image/jfif') {
            $mime = 'image/jpeg';
        }
        if ($mime === '' || ! str_starts_with($mime, 'image/')) {
            $mime = match ($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg', 'jfif' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * Resize (max edge), return data URL only — nothing written under public/uploads.
     */
    private function toOptimizedDataUrl(UploadedFile $file): string
    {
        $contents = $file->getContent();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = (string) $file->getClientMimeType();

        if (! extension_loaded('gd')) {
            return $this->rawDataUrl($contents, $ext, $mime);
        }

        $img = @imagecreatefromstring($contents);
        if ($img === false) {
            return $this->rawDataUrl($contents, $ext, $mime);
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $max = self::MAX_EDGE;

        if ($w > $max || $h > $max) {
            $ratio = min($max / $w, $max / $h);
            $nw = max(1, (int) round($w * $ratio));
            $nh = max(1, (int) round($h * $ratio));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
            imagealphablending($dst, true);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $dst;
        }

        $usePng = $ext === 'png' || str_contains(strtolower($mime), 'png');

        ob_start();
        if ($usePng) {
            imagesavealpha($img, true);
            imagepng($img, null, 6);
            $outMime = 'image/png';
        } else {
            imagejpeg($img, null, self::JPEG_QUALITY);
            $outMime = 'image/jpeg';
        }
        $binary = ob_get_clean();
        imagedestroy($img);

        return 'data:'.$outMime.';base64,'.base64_encode($binary);
    }

    /**
     * Upload one or more images. Returns data URLs stored in the database by the client (no disk files).
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate([
            'images' => 'required|array|max:6',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,jfif|max:5120',
        ]);

        $urls = [];
        foreach ($request->file('images') as $file) {
            $urls[] = $this->toOptimizedDataUrl($file);
        }

        return response()->json(['urls' => $urls]);
    }
}
