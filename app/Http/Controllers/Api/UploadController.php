<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
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

    /**
     * Upload one or more images. Returns array of public URLs.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        $urls = [];
        $uploadDir = public_path('uploads');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        foreach ($request->file('images') as $file) {
            $filename = 'img_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            // Absolute URL pointing to public/uploads so it matches production
            $urls[] = url('/public/uploads/'.$filename);
        }
        return response()->json(['urls' => $urls]);
    }
}
