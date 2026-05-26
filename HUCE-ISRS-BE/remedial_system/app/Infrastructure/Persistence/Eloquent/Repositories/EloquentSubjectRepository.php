<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Entities\Subject;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Mappers\SubjectMapper;
use App\Models\Department as DepartmentModel;
use App\Models\Subject as SubjectModel;

class EloquentSubjectRepository implements SubjectRepositoryPort
{
    public function findById(int $id): ?Subject
    {
        $model = SubjectModel::where('is_deleted', false)->find($id);

        return $model ? SubjectMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?Subject
    {
        $model = SubjectModel::where('subject_code', strtoupper(trim($code)))
            ->where('is_deleted', false)
            ->first();

        return $model ? SubjectMapper::toDomain($model) : null;
    }

    public function findAll(): array
    {
        return SubjectModel::where('is_deleted', false)
            ->orderBy('subject_code')
            ->get()
            ->map(fn ($model) => SubjectMapper::toDomain($model))
            ->all();
    }

    public function save(Subject $subject): Subject
    {
        $model = $subject->id
            ? SubjectModel::where('is_deleted', false)->findOrFail($subject->id)
            : new SubjectModel();

        $model->fill(SubjectMapper::toModelAttributes($subject));
        $model->save();

        return SubjectMapper::toDomain($model->fresh());
    }

    public function softDelete(int $id): void
    {
        SubjectModel::whereKey($id)->update(['is_deleted' => true]);
    }

    public function updateOrCreateSubject(string $subjectCode, array $data): SubjectModel
    {
        return SubjectModel::updateOrCreate(
            ['subject_code' => strtoupper(trim($subjectCode))],
            $data
        );
    }

    public function firstOrCreateDepartment(string $deptCode, array $data): DepartmentModel
    {
        return DepartmentModel::firstOrCreate(
            ['department_code' => $deptCode],
            array_merge([
                'faculty_code' => $deptCode,
                'faculty_name' => null,
                'is_deleted'   => false,
            ], $data)
        );
    }
}
