<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Điểm danh
 */
class Attendance
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $enrollmentId,
        public readonly ?Carbon $studyDate = null,
        public readonly ?bool   $isPresent = null,
        public readonly ?string $note = null,
    ) {}
}
