<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\TutoringClass;

/**
 * Port (Interface) cho Repository quản lý lớp phụ đạo.
 */
interface TutoringClassRepositoryPort
{
    public function findById(int $id): ?TutoringClass;

    /**
     * @return TutoringClass[]
     */
    public function findAll(array $filters = []): array;

    public function save(TutoringClass $tutoringClass): TutoringClass;

    public function delete(int $id): bool;

    /**
     * Đếm số sinh viên đã ghi danh vào lớp.
     */
    public function countEnrollments(int $tutoringClassId): int;
}
