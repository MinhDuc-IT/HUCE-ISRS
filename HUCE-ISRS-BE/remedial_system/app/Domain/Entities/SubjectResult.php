<?php

namespace App\Domain\Entities;

/**
 * Kết quả học tập một môn từ University System (Anti-Corruption Layer).
 */
class SubjectResult
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

    /** Mã tra cứu thống nhất (ưu tiên courseCode từ API trường). */
    public function code(): string
    {
        return $this->courseCode !== '' ? $this->courseCode : $this->subjectCode;
    }

    public function isFailed(): bool
    {
        if ($this->letterGrade === 'F') {
            return true;
        }

        return ($this->finalScore !== null) && $this->finalScore < 5.0;
    }

    public function isEligibleForRemedial(): bool
    {
        return $this->isFailed();
    }
}
