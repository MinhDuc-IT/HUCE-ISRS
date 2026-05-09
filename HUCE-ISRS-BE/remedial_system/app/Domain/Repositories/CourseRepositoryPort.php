<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Course;

/**
 * Port (Interface) cho Repository quản lý môn học.
 */
interface CourseRepositoryPort
{
    public function findById(int $id): ?Course;

    public function findByCode(string $code): ?Course;

    /**
     * @return Course[]
     */
    public function findAll(): array;

    /**
     * Helper methods for synchronization
     */
    public function updateOrCreateCourse(string $courseCode, array $data): \App\Models\Course;

    public function firstOrCreateDepartment(string $deptCode, array $data): \App\Models\Department;
}
