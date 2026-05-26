<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Entities\Department;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Mappers\DepartmentMapper;
use App\Models\Department as DepartmentModel;

class EloquentDepartmentRepository implements DepartmentRepositoryPort
{
    public function findById(int $id): ?Department
    {
        $model = DepartmentModel::where('is_deleted', false)->find($id);

        return $model ? DepartmentMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?Department
    {
        $model = DepartmentModel::where('department_code', $code)
            ->where('is_deleted', false)
            ->first();

        return $model ? DepartmentMapper::toDomain($model) : null;
    }

    public function findAll(): array
    {
        return DepartmentModel::where('is_deleted', false)
            ->orderBy('department_code')
            ->get()
            ->map(fn ($model) => DepartmentMapper::toDomain($model))
            ->all();
    }

    public function save(Department $department): Department
    {
        $model = $department->id
            ? DepartmentModel::where('is_deleted', false)->findOrFail($department->id)
            : new DepartmentModel();

        $model->fill(DepartmentMapper::toModelAttributes($department));
        $model->save();

        return DepartmentMapper::toDomain($model->fresh());
    }

    public function softDelete(int $id): void
    {
        DepartmentModel::whereKey($id)->update(['is_deleted' => true]);
    }
}
