<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/** Domain entity – đăng ký phụ đạo ({@see remedial_registrations}). */
class RemedialRegistration
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $studentId,
        public readonly int     $subjectId,
        public readonly int     $remedialTermId,
        public readonly int     $remedialPeriods,
        public readonly Carbon  $registrationDate,
        public readonly ?string $lectureName = null,
        public readonly ?string $lecturerPhoneNumber = null,
        public readonly ?string $lecturerEmail = null,
    ) {}
}
