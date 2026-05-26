<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Department;
use App\Models\Department as DepartmentModel;
use Carbon\Carbon;

final class DepartmentMapper
{
    public static function toDomain(DepartmentModel $model): Department
    {
        return new Department(
            id:             $model->id,
            departmentCode: $model->department_code,
            departmentName: $model->name,
            facultyCode:    $model->faculty_code,
            facultyName:    $model->faculty_name,
            email:          $model->email,
            phoneNumber:    $model->phone_number,
            createdAt:      Carbon::parse($model->created_at),
        );
    }

    public static function toModelAttributes(Department $entity): array
    {
        return [
            'department_code' => $entity->departmentCode,
            'faculty_code'    => $entity->facultyCode ?? $entity->departmentCode,
            'faculty_name'    => $entity->facultyName,
            'name'            => $entity->departmentName,
            'email'           => $entity->email,
            'phone_number'    => $entity->phoneNumber,
            'is_deleted'      => false,
        ];
    }
}
