<?php

namespace App\Infrastructure\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Domain\Exceptions\ExternalSystemException;

/**
 * UniversityAuthClient – Quản lý xác thực machine-to-machine với University System.
 *
 * Chịu trách nhiệm:
 * - Lấy access_token từ University System qua Client Credentials Flow
 * - Cache token trong Redis để tránh gọi API liên tục
 * - Tự động làm mới token khi hết hạn
 *
 * Đây là thành phần thuần Infrastructure, không có business logic.
 */
class UniversityAuthClient
{
    /**
     * Cache key lưu trữ access token.
     */
    private const CACHE_KEY = 'university_auth:access_token';

    /**
     * Số giây đệm trước khi hết hạn để làm mới token sớm.
     */
    private const REFRESH_BUFFER_SECONDS = 60;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly int    $timeoutSeconds = 10,
    ) {}

    /**
     * Lấy access_token hợp lệ (từ cache hoặc gọi API mới).
     *
     * @return string Access token hợp lệ
     * @throws ExternalSystemException Nếu không lấy được token
     */
    public function getToken(): string
    {
        // Kiểm tra cache trước
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached;
        }

        return $this->fetchNewToken();
    }

    /**
     * Gọi API /token để lấy token mới và lưu vào cache.
     *
     * @throws ExternalSystemException
     */
    private function fetchNewToken(): string
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->retry(3, 500)         // Retry 3 lần, mỗi lần cách 500ms
                ->post("{$this->baseUrl}/api/token", [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]);

            if (! $response->successful()) {
                Log::error('[UniversityAuthClient] Lấy token thất bại', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new ExternalSystemException(
                    'Xác thực University System thất bại: ' . $response->json('message', 'Unknown error')
                );
            }

            $data      = $response->json('data');
            $token     = $data['access_token'];
            $expiresIn = (int) ($data['expires_in'] ?? 3600);

            // Cache token trừ đi buffer để làm mới trước khi hết hạn
            $ttl = max(60, $expiresIn - self::REFRESH_BUFFER_SECONDS);
            Cache::put(self::CACHE_KEY, $token, $ttl);

            Log::info('[UniversityAuthClient] Token mới đã được lấy và cache', [
                'expires_in' => $expiresIn,
                'cached_ttl' => $ttl,
            ]);

            return $token;
        } catch (ExternalSystemException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('[UniversityAuthClient] Lỗi kết nối', ['error' => $e->getMessage()]);
            throw new ExternalSystemException('Không thể kết nối đến University System: ' . $e->getMessage());
        }
    }

    /**
     * Xóa token đang cache (dùng khi token bị từ chối – 401).
     */
    public function invalidateToken(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('[UniversityAuthClient] Token đã bị xóa khỏi cache.');
    }
}
