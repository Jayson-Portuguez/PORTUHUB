<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminSession;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const COOKIE_NAME = 'admin_session';
    private const SESSION_DAYS = 7;

    public function me(): JsonResponse
    {
        $token = request()->cookie(self::COOKIE_NAME);
        if (! $token) {
            return response()->json(['admin' => false]);
        }
        $session = AdminSession::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        return response()->json(['admin' => (bool) $session]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $user = AdminUser::where('username', $request->input('username'))->first();
        if (! $user || ! Hash::check($request->input('password'), $user->password_hash)) {
            return response()->json(['error' => 'Invalid username or password'], 401);
        }
        $token = Str::random(32);
        $expiresAt = now()->addDays(self::SESSION_DAYS);
        AdminSession::create([
            'admin_user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
        $cookie = cookie(
            self::COOKIE_NAME,
            $token,
            now()->addDays(self::SESSION_DAYS)->diffInMinutes(now()),
            '/',
            null,
            request()->secure(),
            true,
            false,
            'lax'
        );
        return response()->json(['success' => true])->cookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->cookie(self::COOKIE_NAME);
        if ($token) {
            AdminSession::where('token', $token)->delete();
        }
        return response()->json(['success' => true])->cookie(Cookie::forget(self::COOKIE_NAME));
    }
}
