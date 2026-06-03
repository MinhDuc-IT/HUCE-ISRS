<?php

namespace App\Events;

class LecturerAssignedToSubject
{
    public function __construct(
        public readonly int $subjectId,
        public readonly int $departmentId,
        public readonly string $lecturerEmail,
        public readonly ?string $lecturerName,
        public readonly ?string $lecturerPhoneNumber,
        public readonly int $updatedCount,
        public readonly ?string $assignedBy,
    ) {}
}
