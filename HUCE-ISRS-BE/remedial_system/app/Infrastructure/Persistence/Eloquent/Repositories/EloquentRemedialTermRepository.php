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
        $models = RemedialTermModel::whereIn('status', [
            \App\Domain\Enums\RemedialTermStatus::REGISTRATION_OPEN->value,
            \App\Domain\Enums\RemedialTermStatus::ACTIVE->value
        ])
        ->orderByDesc('year')
        ->orderByDesc('semester')
        ->orderByDesc('id')
        ->get();

        if ($models->isEmpty()) {
            return null;
        }

        $terms = $models->map(fn ($model) => RemedialTermMapper::toDomain($model));

        // 1. Ưu tiên đợt đang chính thức mở đăng ký theo thời gian
        $openTerm = $terms->first(fn ($term) => $term->getLogicStatus() === \App\Domain\Enums\RemedialTermLogicStatus::REGISTRATION_OPEN);
        if ($openTerm !== null) {
            return $openTerm;
        }

        // 2. Nếu không có đợt mở đăng ký, lấy đợt đang active (sắp mở/đang học/...)
        $activeTerm = $terms->first(fn ($term) => in_array($term->getLogicStatus(), [
            \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_PENDING_REGISTRATION,
            \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_PENDING_CLASS,
            \App\Domain\Enums\RemedialTermLogicStatus::ACTIVE_IN_PROGRESS,
        ], true));

        if ($activeTerm !== null) {
            return $activeTerm;
        }

        // 3. Fallback (e.g. đã hết thời gian nhưng DB chưa update sang COMPLETED)
        return $terms->first();
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
