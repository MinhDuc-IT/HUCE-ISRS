<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\TutoringClass;
use App\Domain\Repositories\TutoringClassRepositoryPort;
use App\Models\TutoringClass as EloquentTutoringClass;
use App\Domain\Enums\TutoringClassStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentTutoringClassRepository implements TutoringClassRepositoryPort
{
    public function findById(int $id): ?TutoringClass
    {
        $model = EloquentTutoringClass::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(array $filters = []): array
    {
        $query = EloquentTutoringClass::query();

        if (isset($filters['tutoring_term_id'])) {
            $query->where('TutoringTermId', $filters['tutoring_term_id']);
        }

        if (isset($filters['status'])) {
            $query->where('Status', $filters['status']);
        }

        return $query->get()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function save(TutoringClass $entity): TutoringClass
    {
        $model = new EloquentTutoringClass();
        
        if ($entity->id !== null) {
            $model = EloquentTutoringClass::find($entity->id) ?? new EloquentTutoringClass();
        }

        $model->CourseId        = $entity->courseId;
        $model->TutoringTermId  = $entity->tutoringTermId;
        $model->TeacherId       = $entity->teacherId;
        $model->MaxStudents     = $entity->maxStudents;
        $model->Status          = $entity->status->value;
        $model->CreatedAt       = $entity->createdAt->toDateTimeString();

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = EloquentTutoringClass::find($id);
        if (!$model) return false;
        return $model->delete();
    }

    public function countEnrollments(int $tutoringClassId): int
    {
        return DB::table('Enrollment')
            ->where('TutoringClassId', $tutoringClassId)
            ->where('Status', 'active')
            ->count();
    }

    private function toDomainEntity(object $model): TutoringClass
    {
        return new TutoringClass(
            $model->Id,
            $model->CourseId,
            $model->TutoringTermId,
            $model->TeacherId,
            $model->MaxStudents,
            $this->countEnrollments($model->Id),
            TutoringClassStatus::from((int)$model->Status),
            Carbon::parse($model->CreatedAt),
            $model->UpdatedAt ? Carbon::parse($model->UpdatedAt) : null
        );
    }
}
