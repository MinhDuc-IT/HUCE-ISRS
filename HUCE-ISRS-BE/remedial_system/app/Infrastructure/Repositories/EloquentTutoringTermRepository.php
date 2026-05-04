<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\TutoringTerm;
use App\Domain\Repositories\TutoringTermRepositoryPort;
use App\Models\TutoringTerm as EloquentTutoringTerm;
use Carbon\Carbon;

class EloquentTutoringTermRepository implements TutoringTermRepositoryPort
{
    public function findById(int $id): ?TutoringTerm
    {
        $model = EloquentTutoringTerm::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findDefault(): ?TutoringTerm
    {
        $model = EloquentTutoringTerm::where('IsDefault', true)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return EloquentTutoringTerm::all()->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    private function toDomainEntity(EloquentTutoringTerm $model): TutoringTerm
    {
        return new TutoringTerm(
            id:          $model->Id,
            semesterId:  $model->SemesterId,
            name:        $model->Name,
            startDate:   $model->StartDate, // Eloquent already casts to Carbon
            endDate:     $model->EndDate,
            heSoPD:      $model->HeSoPD,
            donGia1Tiet: $model->DonGia1Tiet,
            heSoDonGia:  $model->HeSoDonGia,
            isDefault:   $model->IsDefault,
            createdAt:   $model->CreatedAt
        );
    }
}
