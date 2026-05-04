<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Học kỳ
 *
 * Tương ứng bảng SEMESTER trong ERD.
 *
 * @property int    $id          ID học kỳ
 * @property string $name        Tên học kỳ (VD: "Học kỳ 1 – 2024-2025")
 * @property int    $year        Năm học bắt đầu (VD: 2024)
 * @property int    $termNumber  Số thứ tự học kỳ trong năm (1 = HK1, 2 = HK2, 3 = Hè)
 * @property bool   $isActive    Học kỳ hiện đang mở đăng ký
 */
class Semester
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?string $name = null,
        public readonly ?int    $year = null,
        public readonly ?int    $termNumber = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly bool    $isActive = true,
    ) {}

    /**
     * Khoá học (VD: 2024-2025)
     */
    public function academicYearLabel(): string
    {
        return "{$this->year}-" . ($this->year + 1);
    }

    /**
     * Mã học kỳ dạng YYYYT (VD: 20241 cho HK1 năm 2024)
     */
    public function semesterKey(): string
    {
        return "{$this->year}{$this->termNumber}";
    }

    /**
     * Chỉ học kỳ đang hoạt động mới cho phép mở đăng ký mới.
     */
    public function allowsRegistration(): bool
    {
        return $this->isActive;
    }
}
