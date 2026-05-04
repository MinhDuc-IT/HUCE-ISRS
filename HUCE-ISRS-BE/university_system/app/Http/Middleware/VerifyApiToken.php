<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware xác thực Bearer Token cho các API tích hợp hệ thống.
 * Kiểm tra token có tồn tại trong cache (Redis) hay không.
 */
class VerifyApiToken
{
    /**
     * Xử lý request đến.
     *
     * @param Request $request Request đầu vào
     * @param Closure $next    Middleware tiếp theo
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực: Thiếu Authorization header',
                'data'    => null,
                'errors'  => null,
            ], 401);
        }

        $token = substr($authHeader, 7);
        $key   = 'api_token:' . $token;

        if (! cache()->has($key)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực: Token không hợp lệ hoặc đã hết hạn',
                'data'    => null,
                'errors'  => null,
            ], 401);
        }

        // Gắn thông tin client vào request để dùng ở controller nếu cần
        $request->attributes->set('api_client', cache()->get($key));

        return $next($request);
    }
}
