<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Entities\RemedialTerm;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Mappers\RemedialTermMapper;
use App\Models\RemedialRegistration as RemedialRegistrationModel;
use App\Models\RemedialTerm as RemedialTermModel;

class EloquentRemedialTermRepository implements RemedialTermRepositoryPort
{
    public function findById(int $id): ?RemedialTerm
    {
        $model = RemedialTermModel::find($id);

        return $model ? RemedialTermMapper::toDomain($model) : null;
    }

    public function findCurrent(): ?RemedialTerm
    {
//         $model = RemedialTermModel::where('is_current_term', true)->first();

        $model = RemedialTermModel::where('status', \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN->value)
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->first();

        if ($model === null) {
            $model = RemedialTermModel::where('status', \App\Domain\Enums\RemedialTermStatus::ACTIVE->value)
                ->orderByDesc('year')
                ->orderByDesc('id')
                ->first();
        }

        return $model ? RemedialTermMapper::toDomain($model) : null;
    }

    public function findAll(): array
    {
        return RemedialTermModel::query()
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($model) => RemedialTermMapper::toDomain($model))
            ->all();
    }

    public function save(RemedialTerm $term): RemedialTerm
    {
        $model = $term->id
            ? RemedialTermModel::findOrFail($term->id)
            : new RemedialTermModel();

        $model->fill(RemedialTermMapper::toModelAttributes($term));
        $model->save();

        return RemedialTermMapper::toDomain($model->fresh());
    }

    public function softDelete(int $id): void
    {
        RemedialTermModel::whereKey($id)->update(['is_deleted' => true]);
    }

//     public function clearCurrentTermExcept(?int $exceptId = null): void
//     {
//         $query = RemedialTermModel::where('is_current_term', true);
//
//         if ($exceptId !== null) {
//             $query->where('id', '!=', $exceptId);
//         }
//
//         $query->update(['is_current_term' => false]);
//     }

    public function hasActiveRegistrations(int $id): bool
    {
        return RemedialRegistrationModel::where('remedial_term_id', $id)
            ->where('is_deleted', false)
            ->exists();
    }
}
