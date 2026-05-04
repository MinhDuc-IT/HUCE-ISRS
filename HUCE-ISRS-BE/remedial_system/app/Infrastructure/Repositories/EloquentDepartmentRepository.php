<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Department;
use App\Domain\Repositories\DepartmentRepositoryPort;
use App\Models\Department as EloquentDepartment;

use Carbon\Carbon;

class EloquentDepartmentRepository implements DepartmentRepositoryPort
{
    public function findById(int $id): ?Department
    {
        $model = EloquentDepartment::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return EloquentDepartment::all()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    private function toDomainEntity(EloquentDepartment $model): Department
    {
        return new Department(
            id:             $model->Id,
            departmentCode: $model->DepartmentCode,
            departmentName: $model->Name,
            email:          $model->Email ?? null,
            createdAt:      $model->CreatedAt ? Carbon::parse($model->CreatedAt) : new Carbon()
        );
    }
}
