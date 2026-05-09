<?php

namespace App\Infrastructure\Common;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN   = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    public function __construct(
        private readonly string $serviceName,
        private readonly int    $failureThreshold = 5,
        private readonly int    $resetTimeoutSeconds = 60,
    ) {}

    /**
     * Thực hiện một action với sự bảo vệ của Circuit Breaker.
     */
    public function execute(callable $action): mixed
    {
        if ($this->isOpen()) {
            Log::warning("[CircuitBreaker] Service '{$this->serviceName}' đang bị ngắt (OPEN).");
            throw new \RuntimeException("Hệ thống bên ngoài hiện đang không khả dụng. Vui lòng thử lại sau.");
        }

        try {
            $result = $action();
            $this->recordSuccess();
            return $result;
        } catch (\Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function isOpen(): bool
    {
        $state = Cache::get($this->getKey('state'), self::STATE_CLOSED);
        
        if ($state === self::STATE_OPEN) {
            $lastFailureTime = Cache::get($this->getKey('last_failure_time'), 0);
            if (time() - $lastFailureTime > $this->resetTimeoutSeconds) {
                // Thử lại (Half-Open)
                return false;
            }
            return true;
        }

        return false;
    }

    private function recordSuccess(): void
    {
        Cache::forget($this->getKey('failure_count'));
        Cache::put($this->getKey('state'), self::STATE_CLOSED);
    }

    private function recordFailure(): void
    {
        $count = Cache::increment($this->getKey('failure_count'));
        
        if ($count >= $this->failureThreshold) {
            Log::error("[CircuitBreaker] Ngắt mạch (OPEN) cho service '{$this->serviceName}'!");
            Cache::put($this->getKey('state'), self::STATE_OPEN);
            Cache::put($this->getKey('last_failure_time'), time());
        }
    }

    private function getKey(string $suffix): string
    {
        return "circuit_breaker:{$this->serviceName}:{$suffix}";
    }
}
