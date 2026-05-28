<?php

namespace App\Http\Resources;

use App\Domain\Entities\RemedialTerm as RemedialTermEntity;
use Illuminate\Http\Request;

class RemedialTermResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $term = $this->resource;

        if ($term instanceof RemedialTermEntity) {
            return [
                'id'                   => $term->id,
                'name'                 => $term->name,
                'year'                 => $term->year,
                'semester'             => $term->semester,
                'start_date'           => $term->startDate?->toIso8601String(),
                'end_date'             => $term->endDate?->toIso8601String(),
                'registration_start'   => $term->registrationStart?->toIso8601String(),
                'registration_end'     => $term->registrationEnd?->toIso8601String(),
                'remedial_coefficient' => $term->remedialCoefficient,
                'price_per_period'     => $term->pricePerPeriod,
                'price_coefficient'    => $term->priceCoefficient,
                // 'is_current_term'      => ($term->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT)->isCurrent(), // legacy
                'status'               => $term->status->value,
                'status_name'          => $term->status->description(),
            ];
        }

        return [
            'id'                   => $term->id,
            'name'                 => $term->name,
            'year'                 => $term->year,
            'semester'             => $term->semester,
            'start_date'           => $term->start_date?->toIso8601String(),
            'end_date'             => $term->end_date?->toIso8601String(),
            'registration_start'   => $term->registration_start?->toIso8601String(),
            'registration_end'     => $term->registration_end?->toIso8601String(),
            'remedial_coefficient' => $term->remedial_coefficient,
            'price_per_period'     => $term->price_per_period,
            'price_coefficient'    => $term->price_coefficient,
            // 'is_current_term'      => ($term->status ?? \App\Domain\Enums\RemedialTermStatus::DRAFT)->isCurrent(), // legacy
            'status'               => $term->status?->value ?? \App\Domain\Enums\RemedialTermStatus::DRAFT->value,
            'status_name'          => $term->status ? $term->status->description() : \App\Domain\Enums\RemedialTermStatus::DRAFT->description(),
            'created_at'           => $term->created_at?->toIso8601String(),
            'updated_at'           => $term->updated_at?->toIso8601String(),
        ];
    }
}
