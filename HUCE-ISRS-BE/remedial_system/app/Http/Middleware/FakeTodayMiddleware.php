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
    /** null = không fake; YYYY-MM-DD khi cần test local với DB quá khứ */
    private ?string $fakeToday;

    public function __construct()
    {
        // $value = env('FAKE_TODAY');
        $value = '2024-04-20';
        $this->fakeToday = is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        if ($this->fakeToday !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fakeToday) === 1) {
            Carbon::setTestNow(Carbon::parse($this->fakeToday)->startOfDay());
        }

        return $next($request);
    }
}
