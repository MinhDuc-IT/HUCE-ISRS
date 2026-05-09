<?php

namespace App\Domain\Entities;

use App\Domain\Enums\EnrollmentStatus;
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
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $studentId,
        public readonly int     $tutoringClassId,
        public EnrollmentStatus|int $status = EnrollmentStatus::ACTIVE,
        public readonly Carbon  $enrolledAt = new Carbon(),
    ) {
        if (is_int($status)) {
            $status = EnrollmentStatus::from($status);
        }
        $this->status = $status;
    }

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
