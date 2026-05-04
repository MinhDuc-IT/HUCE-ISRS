<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Kết quả học tập một môn của sinh viên (Anti-Corruption Layer)
 *
 * Đây là representation nội bộ, độc lập với cấu trúc của University System.
 *
 * @property string      $courseCode       Mã học phần
 * @property string      $subjectCode      Mã môn học
 * @property string      $subjectName      Tên môn học
 * @property int         $credits          Số tín chỉ
 * @property string      $classSectionCode Mã lớp học phần
 * @property int         $semesterOrder    Thứ tự học kỳ
 * @property int         $academicYear     Năm học
 * @property float|null  $finalScore       Điểm tổng kết hệ 10
 * @property float|null  $gpaScore         Điểm tổng kết hệ 4
 * @property string|null $letterGrade      Điểm chữ
 */
class CourseResult
{
    public function __construct(
        public readonly string  $courseCode,
        public readonly string  $subjectCode,
        public readonly string  $subjectName,
        public readonly int     $credits,
        public readonly string  $classSectionCode,
        public readonly int     $semesterOrder,
        public readonly int     $academicYear,
        public readonly ?float  $finalScore,
        public readonly ?float  $gpaScore,
        public readonly ?string $letterGrade,
    ) {}

    /**
     * Kiểm tra môn này có bị rớt (điểm chữ F hoặc điểm dưới 5).
     */
    public function isFailed(): bool
    {
        if ($this->letterGrade === 'F') {
            return true;
        }

        return ($this->finalScore !== null) && $this->finalScore < 5.0;
    }

    /**
     * Kiểm tra môn có thể đăng ký học bổ sung.
     */
    public function isEligibleForRemedial(): bool
    {
        return $this->isFailed();
    }
}
