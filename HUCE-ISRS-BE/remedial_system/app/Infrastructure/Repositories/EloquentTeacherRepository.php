<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Teacher;
use App\Domain\Repositories\TeacherRepositoryPort;
use App\Models\Teacher as EloquentTeacher;

class EloquentTeacherRepository implements TeacherRepositoryPort
{
    public function findById(int $id): ?Teacher
    {
        $model = EloquentTeacher::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCode(string $code): ?Teacher
    {
        $model = EloquentTeacher::where('TeacherCode', strtoupper(trim($code)))->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return EloquentTeacher::all()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    private function toDomainEntity(EloquentTeacher $model): Teacher
    {
        return new Teacher(
            id:           $model->Id,
            teacherCode:  $model->TeacherCode,
            fullName:     $model->FullName,
            email:        $model->Email,
            departmentId: $model->DepartmentId
        );
    }
}
