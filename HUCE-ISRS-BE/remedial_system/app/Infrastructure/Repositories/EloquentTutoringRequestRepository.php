<?php

namespace App\Infrastructure\Repositories;

use App\Models\TutoringRequest as EloquentTutoringRequest;
use App\Domain\Entities\TutoringRequest;
use App\Domain\Repositories\TutoringRequestRepositoryPort;
use Carbon\Carbon;

/**
 * Implementation của TutoringRequestRepositoryPort sử dụng Eloquent.
 */
class EloquentTutoringRequestRepository implements TutoringRequestRepositoryPort
{
    public function save(TutoringRequest $request): TutoringRequest
    {
        $model = new EloquentTutoringRequest();
        
        if ($request->id !== null) {
            $model = EloquentTutoringRequest::find($request->id) ?? new EloquentTutoringRequest();
        }

        $model->StudentId        = $request->studentId;
        $model->CourseId         = $request->courseId;
        $model->TutoringTermId   = $request->tutoringTermId;
        $model->RequestedPeriods = $request->requestedPeriods;
        $model->Status           = $request->status;
        $model->Note             = $request->note;
        $model->CreatedAt        = $request->createdAt->toDateTimeString();

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function update(TutoringRequest $request): void
    {
        $model = EloquentTutoringRequest::find($request->id);
        
        if ($model) {
            $model->Status = $request->status;
            $model->Note   = $request->note;
            $model->save();
        }
    }

    public function findById(int $id): ?TutoringRequest
    {
        $model = EloquentTutoringRequest::find($id);

        if (! $model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function existsActiveRequest(int $studentId, int $courseId, int $tutoringTermId): bool
    {
        return EloquentTutoringRequest::where('StudentId', $studentId)
            ->where('CourseId', $courseId)
            ->where('TutoringTermId', $tutoringTermId)
            ->whereIn('Status', [TutoringRequest::STATUS_PENDING, TutoringRequest::STATUS_APPROVED])
            ->exists();
    }

    public function findByStudent(int $studentId): array
    {
        $models = EloquentTutoringRequest::where('StudentId', $studentId)
            ->orderBy('CreatedAt', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity((object)$model))->toArray();
    }

    /**
     * Map từ Eloquent Model sang Domain Entity.
     */
    private function toDomainEntity(object $model): TutoringRequest
    {
        return new TutoringRequest(
            id:               $model->Id, // Tùy thuộc vào ORM map ID thế nào, Eloquent mặc định cột khóa chính là 'id' lowercase trừ khi khai báo khác
            studentId:        $model->StudentId,
            courseId:         $model->CourseId,
            tutoringTermId:   $model->TutoringTermId,
            requestedPeriods: $model->RequestedPeriods,
            status:           $model->Status,
            note:             $model->Note,
            createdAt:        Carbon::parse($model->CreatedAt),
        );
    }
}
