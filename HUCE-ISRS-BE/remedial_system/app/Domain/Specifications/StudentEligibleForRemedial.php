<?php

namespace App\Domain\Specifications;

use App\Domain\Entities\StudentInfo;

class StudentEligibleForRemedial implements Specification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof StudentInfo && $candidate->isEligibleForRemedial();
    }

    public function getMessage(): string
    {
        return 'Sinh viên không đủ điều kiện học phụ đạo (trạng thái không hợp lệ).';
    }
}
