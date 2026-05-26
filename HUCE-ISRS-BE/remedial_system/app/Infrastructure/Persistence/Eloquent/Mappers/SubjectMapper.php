<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Subject;
use App\Models\Subject as SubjectModel;

final class SubjectMapper
{
    public static function toDomain(SubjectModel $model): Subject
    {
        return new Subject(
            id:           $model->id,
            subjectCode:  $model->subject_code,
            name:         $model->name,
            credits:      $model->credits,
            departmentId: $model->department_id,
        );
    }

    public static function toModelAttributes(Subject $entity): array
    {
        return [
            'subject_code'  => $entity->subjectCode,
            'name'          => $entity->name,
            'credits'       => $entity->credits,
            'department_id' => $entity->departmentId,
            'is_deleted'    => false,
        ];
    }
}
