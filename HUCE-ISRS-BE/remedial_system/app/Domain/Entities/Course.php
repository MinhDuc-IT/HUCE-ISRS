<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Môn học trong hệ thống học bổ sung
 *
 * Tương ứng bảng COURSE trong ERD.
 *
 * @property int    $id             ID môn học
 * @property string $courseCode     Mã môn học (VD: CS101)
 * @property string $courseName     Tên môn học
 * @property int    $credits        Số tín chỉ
 * @property int    $departmentId   ID khoa quản lý môn này
 */
class Course
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $courseCode,
        public readonly string  $courseName,
        public readonly ?int    $credits = null,
        public readonly ?int    $totalPeriods = null,
        public readonly int     $departmentId = 0,
        public readonly bool    $isActive = true,
    ) {}

    /**
     * Kiểm tra môn học có đủ tín chỉ để mở lớp bổ sung.
     * Quy định: môn từ 2 tín chỉ trở lên mới được tổ chức học bổ sung.
     */
    public function isEligibleForTutoring(): bool
    {
        return $this->credits >= 2;
    }
}
