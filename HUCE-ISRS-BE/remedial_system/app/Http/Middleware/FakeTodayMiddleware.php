<?php

namespace App\Http\Middleware;

use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Giả lập "hôm nay" khi chạy local/testing (DB university test chỉ có data quá khứ).
 * Đọc cấu hình từ DB: key = 'FAKE_DAY', value = 'YYYY-MM-DD'
 * Nếu không có hoặc null → dùng ngày thật.
 */
class FakeTodayMiddleware
{
    public const CONFIG_KEY = 'FAKE_DAY';

    /** Cache static để tránh query DB mỗi request */
    private static ?string $cachedFakeDay = null;
    private static bool $isLoaded = false;

    public function __construct(
        private readonly SystemConfigurationRepositoryPort $configRepository,
    ) {}

    public static function clearCacheForKey(string $key): void
    {
        if ($key !== self::CONFIG_KEY) {
            return;
        }

        self::$cachedFakeDay = null;
        self::$isLoaded = false;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Load cache nếu chưa có (chỉ query 1 lần)
        if (! self::$isLoaded) {
            self::$cachedFakeDay = $this->configRepository->get(self::CONFIG_KEY);
            self::$isLoaded = true;
        }

        // Nếu có giá trị hợp lệ → set fake time
        if (self::$cachedFakeDay !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', self::$cachedFakeDay) === 1) {
            $realNow = Carbon::now()->toDateTimeString();
            
            Carbon::setTestNow(Carbon::parse(self::$cachedFakeDay)->startOfDay());

            Log::info('[FakeTodayMiddleware] Đã giả lập ngày hiện tại', [
                'real_now' => $realNow,
                'effective_now' => Carbon::now()->toDateTimeString(),
                'fake_today' => self::$cachedFakeDay,
                'environment' => app()->environment(),
            ]);
        }

        return $next($request);
    }
}