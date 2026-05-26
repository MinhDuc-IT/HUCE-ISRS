<?php

namespace App\Domain\Ports\Persistence;

use App\Domain\Entities\Department;

interface DepartmentRepositoryPort
{
    public function findById(int $id): ?Department;

    public function findByCode(string $code): ?Department;

    /** @return Department[] */
    public function findAll(): array;

    public function save(Department $department): Department;

    public function softDelete(int $id): void;
}
