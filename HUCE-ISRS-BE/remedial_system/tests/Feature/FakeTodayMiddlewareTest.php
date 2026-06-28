<?php

namespace Tests\Feature;

use App\Http\Middleware\FakeTodayMiddleware;
use Carbon\Carbon;
use Tests\TestCase;

class FakeTodayMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_fake_today_constant_is_valid_date_format(): void
    {
        $fakeToday = (new \ReflectionClass(FakeTodayMiddleware::class))->getConstant('FAKE_TODAY');

        if ($fakeToday === null) {
            $this->markTestSkipped('FAKE_TODAY is null');
        }

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $fakeToday);
        Carbon::setTestNow(Carbon::parse($fakeToday)->startOfDay());
        $this->assertSame($fakeToday, Carbon::now()->toDateString());
    }
}
