<?php

namespace App\Domain\Specifications;

use App\Domain\Entities\Course;

class CourseEligibleForTutoring implements Specification
{
    /**
     * @param Course $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        // Quy định: Chỉ các môn học từ 2 tín chỉ trở lên mới được mở lớp bổ sung
        return $candidate->credits >= 2;
    }

    public function getMessage(): string
    {
        return "Môn học không đủ điều kiện mở lớp bổ sung (Yêu cầu tối thiểu 2 tín chỉ).";
    }
}
