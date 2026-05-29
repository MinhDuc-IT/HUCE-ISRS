<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\RemedialTerm;
use App\Models\RemedialTerm as RemedialTermModel;
use Carbon\Carbon;

final class RemedialTermMapper
{
    public static function toDomain(RemedialTermModel $model): RemedialTerm
    {
        return new RemedialTerm(
            id:                  $model->id,
            year:                $model->year,
            semester:            $model->semester,
            name:                $model->name,
            startDate:           $model->start_date ? Carbon::parse($model->start_date) : null,
            endDate:             $model->end_date ? Carbon::parse($model->end_date) : null,
            remedialCoefficient: $model->remedial_coefficient ?? 1,
            pricePerPeriod:      $model->price_per_period ?? 150000,
            priceCoefficient:    (float) ($model->price_coefficient ?? 1),
            isCurrentTerm:       ($model->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT)->isCurrent(),
            registrationStart:   $model->registration_start ? Carbon::parse($model->registration_start) : null,
            registrationEnd:     $model->registration_end ? Carbon::parse($model->registration_end) : null,
            status:              $model->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT,
        );
    }

    public static function toModelAttributes(RemedialTerm $entity): array
    {
        return [
            'year'                 => $entity->year,
            'semester'             => $entity->semester,
            'name'                 => $entity->name,
            'start_date'           => $entity->startDate,
            'end_date'             => $entity->endDate,
            'registration_start'   => $entity->registrationStart,
            'registration_end'     => $entity->registrationEnd,
            'remedial_coefficient' => $entity->remedialCoefficient,
            'price_per_period'     => $entity->pricePerPeriod,
            'price_coefficient'    => $entity->priceCoefficient,
            'is_current_term'      => $entity->status->isCurrent(),
            'status'               => $entity->status->value,
        ];
    }
}
