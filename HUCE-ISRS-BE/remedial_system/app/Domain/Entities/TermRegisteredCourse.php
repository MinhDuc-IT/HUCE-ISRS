<?php

namespace App\Domain\Entities;

/**
 * Môn học chính quy sinh viên đã đăng ký theo kỳ/năm (từ DT_DangKyHocPhan – University System).
 */
class TermRegisteredCourse
{
    public function __construct(
        public readonly string  $subjectCode,
        public readonly string  $subjectName,
        public readonly int     $credits,
        public readonly string  $classSectionCode,
        public readonly string  $plannedClass,
        public readonly ?string $registrationDate,
        public readonly int     $registrationId,
        public readonly int     $registrationStatusId,
        public readonly string  $registrationStatusName,
        public readonly string  $academicYearLabel,
        public readonly int     $academicYear,
        public readonly int     $semesterOrder,
        public readonly string  $termName,
    ) {}

    public function code(): string
    {
        return $this->subjectCode;
    }
}
