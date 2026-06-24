<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Entities\RemedialRegistration;
use App\Domain\Ports\Persistence\RemedialRegistrationRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Mappers\RemedialRegistrationMapper;
use App\Models\RemedialRegistration as RemedialRegistrationModel;

class EloquentRemedialRegistrationRepository implements RemedialRegistrationRepositoryPort
{
    public function save(RemedialRegistration $registration): RemedialRegistration
    {
        $model = $registration->id
            ? RemedialRegistrationModel::findOrFail($registration->id)
            : new RemedialRegistrationModel();

        $model->fill(RemedialRegistrationMapper::toModelAttributes($registration));
        $model->save();

        return RemedialRegistrationMapper::toDomain($model);
    }

    public function findById(int $id): ?RemedialRegistration
    {
        $model = RemedialRegistrationModel::find($id);

        return $model ? RemedialRegistrationMapper::toDomain($model) : null;
    }

    public function delete(int $id): void
    {
        RemedialRegistrationModel::whereKey($id)->update([
            'is_deleted' => true,
        ]);
    }

    public function existsRegistration(int $userId, int $subjectId, int $remedialTermId): bool
    {
        return RemedialRegistrationModel::where('student_id', $userId)
            ->where('subject_id', $subjectId)
            ->where('remedial_term_id', $remedialTermId)
            ->where('is_deleted', false)
            ->exists();
    }

    public function findByUser(int $userId, ?int $remedialTermId = null): array
    {
        $query = RemedialRegistrationModel::where('student_id', $userId)
            ->where('is_deleted', false);

        if ($remedialTermId !== null) {
            $query->where('remedial_term_id', $remedialTermId);
        }

        return $query->orderByDesc('registration_date')
            ->get()
            ->map(fn ($model) => RemedialRegistrationMapper::toDomain($model))
            ->all();
    }

    public function bulkUpdateLecturerForSubject(int $subjectId, int $departmentId, array $data): int
    {
        $query = RemedialRegistrationModel::query()
            ->where('subject_id', $subjectId)
            ->whereHas('subject', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->where('is_deleted', false));

        $updates = [];

        if (array_key_exists('lecture_name', $data)) {
            $updates['lecture_name'] = $data['lecture_name'];
        }

        if (array_key_exists('lecturer_phone_number', $data)) {
            $updates['lecturer_phone_number'] = $data['lecturer_phone_number'];
        }

        if (array_key_exists('lecturer_email', $data)) {
            $updates['lecturer_emal'] = $data['lecturer_email'];
        }

        if ($updates === []) {
            return 0;
        }

        return $query->update($updates);
    }
}
