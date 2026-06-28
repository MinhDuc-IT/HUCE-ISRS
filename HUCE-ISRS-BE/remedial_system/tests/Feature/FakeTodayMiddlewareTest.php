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

    public function test_fake_today_constant_overrides_now_in_local_environment(): void
    {
        $fakeToday = (new \ReflectionClass(FakeTodayMiddleware::class))->getConstant('FAKE_TODAY');

        // if ($fakeToday === null) {
        //     $this->markTestSkipped('FAKE_TODAY is null');
        // }

        $this->app['router']->get('/api/__test/fake-today', fn () => response()->json([
            'today' => Carbon::now()->toDateString(),
        ]));

        $this->getJson('/api/__test/fake-today')
            ->assertOk()
            ->assertJsonPath('today', $fakeToday);
    }
}
