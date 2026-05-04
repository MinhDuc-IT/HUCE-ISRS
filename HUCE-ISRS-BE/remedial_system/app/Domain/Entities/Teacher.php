<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Giảng viên
 *
 * @property int|null    $id
 * @property string      $teacherCode
 * @property string      $fullName
 * @property string|null $email
 * @property string|null $phone
 * @property int         $departmentId
 * @property int|null    $maxHoursPerWeek
 * @property string|null $status
 * @property Carbon      $createdAt
 * @property Carbon|null $updatedAt
 */
class Teacher
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $teacherCode,
        public readonly string  $fullName,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly int     $departmentId = 0,
        public readonly ?int    $maxHoursPerWeek = 40,
        public readonly ?string $status = null,
        public readonly Carbon  $createdAt = new Carbon(),
        public readonly ?Carbon $updatedAt = null,
    ) {}

    /**
     * Kiểm tra giảng viên có cùng khoa với môn học không.
     * Ưu tiên xếp giảng viên cùng khoa dạy môn trong khoa đó.
     */
    public function isInSameDepartmentAs(Course $course): bool
    {
        return $this->departmentId === $course->departmentId;
    }
}
