<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Course;
use App\Domain\Repositories\CourseRepositoryPort;
use App\Models\Course as EloquentCourse;
use Carbon\Carbon;

class EloquentCourseRepository implements CourseRepositoryPort
{
    public function findById(int $id): ?Course
    {
        $model = EloquentCourse::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCode(string $code): ?Course
    {
        $model = EloquentCourse::where('CourseCode', strtoupper(trim($code)))->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return EloquentCourse::all()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function updateOrCreateCourse(string $courseCode, array $data): EloquentCourse
    {
        return EloquentCourse::updateOrCreate(
            ['CourseCode' => $courseCode],
            $data
        );
    }

    public function firstOrCreateDepartment(string $deptCode, array $data): \App\Models\Department
    {
        return \App\Models\Department::firstOrCreate(
            ['DepartmentCode' => $deptCode],
            $data
        );
    }

    private function toDomainEntity(EloquentCourse $model): Course
    {
        return new Course(
            id:           $model->Id,
            courseCode:   $model->CourseCode,
            courseName:   $model->CourseName,
            credits:      $model->Credits,
            totalPeriods: $model->TotalPeriods ?? null,
            departmentId: $model->DepartmentId,
            isActive:     (bool) ($model->IsActive ?? true),
        );
    }
}
