<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Đơn xin mở lớp phụ đạo
 */
class TutoringRequest
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        public readonly ?int    $id,
        public readonly int     $studentId,
        public readonly int     $courseId,
        public readonly int     $tutoringTermId,
        public readonly ?int    $requestedPeriods = null,
        public string           $status = self::STATUS_PENDING,
        public ?string          $note = null,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {}
}
