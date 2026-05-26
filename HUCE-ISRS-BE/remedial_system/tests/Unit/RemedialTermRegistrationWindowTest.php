<?php

namespace Tests\Unit;

use App\Domain\Entities\RemedialTerm;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class RemedialTermRegistrationWindowTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_registration_stays_open_until_end_of_registration_end_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-21 15:30:00'));

        $term = new RemedialTerm(
            id: 1,
            year: 2025,
            semester: 1,
            name: 'Test',
            registrationStart: Carbon::parse('2026-05-19'),
            registrationEnd: Carbon::parse('2026-05-21'),
        );

        $this->assertTrue($term->isRegistrationOpen());
    }

    public function test_registration_closed_after_registration_end_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-22 00:00:01'));

        $term = new RemedialTerm(
            id: 1,
            year: 2025,
            semester: 1,
            name: 'Test',
            registrationStart: Carbon::parse('2026-05-19'),
            registrationEnd: Carbon::parse('2026-05-21'),
        );

        $this->assertFalse($term->isRegistrationOpen());
    }
}
