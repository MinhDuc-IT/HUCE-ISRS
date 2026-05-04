<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Department;

/**
 * Port (Interface) cho Repository quản lý khoa/bộ môn.
 */
interface DepartmentRepositoryPort
{
    public function findById(int $id): ?Department;

    /**
     * @return Department[]
     */
    public function findAll(): array;
}
