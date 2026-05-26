<?php

namespace App\Application\Services;

use App\Domain\Entities\RemedialRegistration;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Models\User;

final class StudentRegistrationPresenter
{
    public function __construct(
        private readonly SubjectRepositoryPort $subjectRepository,
    ) {}

    public function format(RemedialRegistration $registration, ?User $user = null): array
    {
        $subject = $this->subjectRepository->findById($registration->subjectId);

        return [
            'id'                => $registration->id,
            'student_code'      => $user?->student_code,
            'course_code'       => $subject?->subjectCode ?? 'UNKNOWN',
            'course_name'       => $subject?->name,
            'remedial_term_id'  => $registration->remedialTermId,
            'remedial_periods'  => $registration->remedialPeriods,
            'registration_date' => $registration->registrationDate->toIso8601String(),
            'lecture_name'      => $registration->lectureName,
            'lecturer_phone'    => $registration->lecturerPhoneNumber,
            'lecturer_email'    => $registration->lecturerEmail,
        ];
    }

    /** @param RemedialRegistration[] $registrations */
    public function formatMany(array $registrations, User $user): array
    {
        return array_map(
            fn ($r) => $this->format($r, $user),
            $registrations
        );
    }
}
