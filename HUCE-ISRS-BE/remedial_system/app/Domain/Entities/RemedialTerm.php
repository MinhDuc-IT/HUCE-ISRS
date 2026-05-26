<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/** Domain entity – đợt phụ đạo ({@see remedial_terms}). */
class RemedialTerm
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $year,
        public readonly int     $semester,
        public readonly string  $name,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly int     $remedialCoefficient = 1,
        public readonly int     $pricePerPeriod = 150000,
        public readonly float   $priceCoefficient = 1.0,
        public readonly bool    $isCurrentTerm = false,
        public readonly ?Carbon $registrationStart = null,
        public readonly ?Carbon $registrationEnd = null,
    ) {}

    public function isRegistrationOpen(): bool
    {
        $now = Carbon::now();

        // Admin/FE gửi ngày (YYYY-MM-DD) → DB thường là 00:00:00; coi cả ngày lịch là hợp lệ.
        if ($this->registrationStart !== null) {
            $openFrom = $this->registrationStart->copy()->startOfDay();
            if ($now->lt($openFrom)) {
                return false;
            }
        }

        if ($this->registrationEnd !== null) {
            $openUntil = $this->registrationEnd->copy()->endOfDay();
            if ($now->gt($openUntil)) {
                return false;
            }
        }

        return true;
    }
}
