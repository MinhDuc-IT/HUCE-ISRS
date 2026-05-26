<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Vai trò được phép: admin, bo_mon, sinh_vien
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực. Vui lòng đăng nhập.',
                'data'    => null,
                'errors'  => null,
            ], 401);
        }

        if ($user->is_deleted) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị vô hiệu hóa.',
                'data'    => null,
                'errors'  => null,
            ], 403);
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập tài nguyên này.',
                'data'    => null,
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}
