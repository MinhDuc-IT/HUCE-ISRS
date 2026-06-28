<?php

namespace App\Domain\Ports\Persistence;

use App\Domain\Entities\RemedialRegistration;

interface RemedialRegistrationRepositoryPort
{
    public function save(RemedialRegistration $registration): RemedialRegistration;

    public function findById(int $id): ?RemedialRegistration;

    public function delete(int $id): void;

    public function existsRegistration(int $userId, int $subjectId, int $remedialTermId): bool;

    /** @return RemedialRegistration[] */
    public function findByUser(int $userId, ?int $remedialTermId = null): array;

    public function bulkUpdateLecturerForSubject(
        int $subjectId,
        int $departmentId,
        int $remedialTermId,
        array $data,
    ): int;
}
