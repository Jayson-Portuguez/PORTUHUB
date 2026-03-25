<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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

    private function uploadsDir(): string
    {
        $dir = public_path('uploads');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function extensionForRawSave(string $ext): string
    {
        $ext = strtolower($ext);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
        if (! in_array($ext, $allowed, true)) {
            return 'jpg';
        }
        if ($ext === 'jpeg' || $ext === 'jfif') {
            return 'jpg';
        }

        return $ext;
    }

    /**
     * Resize (max edge), write under public/uploads, return path /uploads/... for DB and URLs.
     */
    private function storeImageToPublic(UploadedFile $file): string
    {
        $dir = $this->uploadsDir();
        $contents = $file->getContent();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getClientMimeType());
        $usePng = $ext === 'png' || str_contains($mime, 'png');
        $outExt = $usePng ? 'png' : 'jpg';
        $basename = 'img_'.Str::random(16).'.'.$outExt;
        $fullPath = $dir.DIRECTORY_SEPARATOR.$basename;

        if (extension_loaded('gd')) {
            $img = @imagecreatefromstring($contents);
            if ($img !== false) {
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

                if ($usePng) {
                    imagesavealpha($img, true);
                    imagepng($img, $fullPath, 6);
                } else {
                    imagejpeg($img, $fullPath, self::JPEG_QUALITY);
                }
                imagedestroy($img);

                return '/uploads/'.$basename;
            }
        }

        $rawExt = $this->extensionForRawSave($ext);
        $basename = 'img_'.Str::random(16).'.'.$rawExt;
        file_put_contents($dir.DIRECTORY_SEPARATOR.$basename, $contents);

        return '/uploads/'.$basename;
    }

    /**
     * Upload one or more images. Returns /uploads/... paths (short strings safe for landing_settings varchar).
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
            $urls[] = $this->storeImageToPublic($file);
        }

        return response()->json(['urls' => $urls]);
    }
}
