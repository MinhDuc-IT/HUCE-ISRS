<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Lịch học
 */
class ClassSchedule
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $tutoringClassId,
        public readonly ?int    $dayOfWeek = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $room = null,
        public ?string          $status = null,
    ) {}
}
