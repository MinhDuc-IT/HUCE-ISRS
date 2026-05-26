<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\RemedialRegistration;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use Carbon\Carbon;

final class RemedialRegistrationMapper
{
    public static function toDomain(RemedialRegistrationModel $model): RemedialRegistration
    {
        return new RemedialRegistration(
            id:                   $model->id,
            studentId:            $model->student_id,
            subjectId:            $model->subject_id,
            remedialTermId:       $model->remedial_term_id,
            remedialPeriods:      $model->remedial_periods,
            registrationDate:     Carbon::parse($model->registration_date),
            lectureName:          $model->lecture_name,
            lecturerPhoneNumber:  $model->lecturer_phone_number,
            lecturerEmail:        $model->lecturer_emal,
        );
    }

    public static function toModelAttributes(RemedialRegistration $entity): array
    {
        return [
            'student_id'            => $entity->studentId,
            'subject_id'            => $entity->subjectId,
            'remedial_term_id'      => $entity->remedialTermId,
            'remedial_periods'      => $entity->remedialPeriods,
            'registration_date'     => $entity->registrationDate,
            'lecture_name'          => $entity->lectureName,
            'lecturer_phone_number' => $entity->lecturerPhoneNumber,
            'lecturer_emal'         => $entity->lecturerEmail,
        ];
    }
}
