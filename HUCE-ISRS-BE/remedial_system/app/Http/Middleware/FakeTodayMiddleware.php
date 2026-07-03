<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Giả lập "hôm nay" khi chạy local/testing (DB university test chỉ có data quá khứ).
 * Đặt null để dùng ngày thật.
 */
class FakeTodayMiddleware
{
    /** null = không fake (dùng ngày thật hôm nay); YYYY-MM-DD khi cần test local với DB quá khứ */
    private const FAKE_TODAY = '2024-02-07';

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        if (self::FAKE_TODAY !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', self::FAKE_TODAY) === 1) {
            Carbon::setTestNow(Carbon::parse(self::FAKE_TODAY)->startOfDay());
        }

        return $next($request);
    }
}
