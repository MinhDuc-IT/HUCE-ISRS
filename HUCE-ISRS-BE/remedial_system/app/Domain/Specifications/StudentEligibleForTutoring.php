<?php

namespace App\Domain\Specifications;

use App\Domain\Entities\StudentInfo;

class StudentEligibleForTutoring implements Specification
{
    /**
     * @param StudentInfo $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return true;
    }

    public function getMessage(): string
    {
        return "Sinh viên không đủ điều kiện học bổ sung (Trạng thái không hợp lệ).";
    }
}
