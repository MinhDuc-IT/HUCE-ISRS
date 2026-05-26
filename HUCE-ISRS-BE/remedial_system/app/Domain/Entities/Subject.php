<?php

namespace App\Domain\Entities;

/** Domain entity – môn học catalog local ({@see subjects}). */
class Subject
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $subjectCode,
        public readonly string  $name,
        public readonly ?int    $credits = null,
        public readonly int     $departmentId = 0,
    ) {}

    public function isEligibleForRemedial(): bool
    {
        return ($this->credits ?? 0) >= 2;
    }
}
