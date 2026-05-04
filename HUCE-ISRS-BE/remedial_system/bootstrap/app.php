<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn () => true);

        // Validation errors – chuẩn hóa response
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

        // Không tìm thấy sinh viên
        $exceptions->render(function (StudentNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'errors'  => null,
                ], 404);
            }
        });

        // Lỗi kết nối University System
        $exceptions->render(function (ExternalSystemException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'errors'  => null,
                ], 503);
            }
        });

        // Domain business rule violations
        $exceptions->render(function (\DomainException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'errors'  => null,
                ], 400);
            }
        });

        // 404 route not found
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
