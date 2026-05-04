<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký middleware xác thực token tùy chỉnh
        $middleware->alias([
            'verify.api.token' => \App\Http\Middleware\VerifyApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý lỗi validation – trả về chuẩn API thống nhất
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'data'    => null,
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Xử lý lỗi không tìm thấy route
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint không tồn tại',
                    'data'    => null,
                    'errors'  => null,
                ], 404);
            }
        });
    })->create();
