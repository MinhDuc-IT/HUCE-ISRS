<?php

namespace Tests\Feature;

use App\Http\Middleware\FakeTodayMiddleware;
use Tests\TestCase;

class FakeTodayMiddlewareTest extends TestCase
{
    public function test_fake_today_config_key_is_expected_key(): void
    {
        $this->assertSame('FAKE_DAY', FakeTodayMiddleware::CONFIG_KEY);
    }

    public function test_clear_cache_only_resets_fake_day_cache_for_matching_key(): void
    {
        $reflection = new \ReflectionClass(FakeTodayMiddleware::class);
        $cachedFakeDay = $reflection->getProperty('cachedFakeDay');
        $isLoaded = $reflection->getProperty('isLoaded');

        $cachedFakeDay->setValue(null, '2026-07-01');
        $isLoaded->setValue(null, true);

        FakeTodayMiddleware::clearCacheForKey('OTHER_KEY');

        $this->assertSame('2026-07-01', $cachedFakeDay->getValue());
        $this->assertTrue($isLoaded->getValue());

        FakeTodayMiddleware::clearCacheForKey(FakeTodayMiddleware::CONFIG_KEY);

        $this->assertNull($cachedFakeDay->getValue());
        $this->assertFalse($isLoaded->getValue());
    }
}
