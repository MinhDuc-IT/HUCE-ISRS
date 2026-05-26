<?php

namespace App\Domain\Specifications;

use App\Domain\Entities\Subject;

class SubjectEligibleForRemedial implements Specification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Subject && $candidate->isEligibleForRemedial();
    }

    public function getMessage(): string
    {
        return 'Môn học không đủ điều kiện mở lớp phụ đạo (yêu cầu tối thiểu 2 tín chỉ).';
    }
}
