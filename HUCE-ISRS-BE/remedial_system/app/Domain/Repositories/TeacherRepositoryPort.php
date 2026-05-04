<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Teacher;

/**
 * Port (Interface) cho Repository quản lý giảng viên.
 */
interface TeacherRepositoryPort
{
    public function findById(int $id): ?Teacher;

    public function findByCode(string $code): ?Teacher;

    /**
     * @return Teacher[]
     */
    public function findAll(): array;
}
