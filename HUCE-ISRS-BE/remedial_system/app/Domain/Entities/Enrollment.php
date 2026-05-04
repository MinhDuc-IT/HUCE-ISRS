<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Ghi danh vào lớp học bổ sung
 *
 * Tương ứng bảng ENROLLMENT trong ERD.
 * Ghi lại việc sinh viên được xếp vào một lớp học bổ sung cụ thể.
 *
 * @property int         $id              ID ghi danh
 * @property int         $studentId       FK → STUDENT (ID nội bộ)
 * @property int         $tutoringClassId FK → TUTORING_CLASS
 * @property Carbon      $enrolledAt      Thời điểm ghi danh
 */
class Enrollment
{
    public const STATUS_ACTIVE = 'active';

    public function __construct(
        public readonly ?int    $id,
        public readonly int     $studentId,
        public readonly int     $tutoringClassId,
        public string           $status = self::STATUS_ACTIVE,
        public readonly Carbon  $enrolledAt = new Carbon(),
    ) {}

    /**
     * Tạo bản ghi ghi danh mới tại thời điểm hiện tại.
     */
    public static function create(int $studentId, int $tutoringClassId): self
    {
        return new self(
            id:              null,
            studentId:       $studentId,
            tutoringClassId: $tutoringClassId,
            enrolledAt:      Carbon::now(),
        );
    }
}
