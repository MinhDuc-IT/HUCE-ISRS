<?php

namespace App\Domain\Ports\Persistence;

use App\Domain\Entities\Subject;
use App\Models\Department as EloquentDepartment;
use App\Models\Subject as EloquentSubject;

interface SubjectRepositoryPort
{
    public function findById(int $id): ?Subject;

    public function findByCode(string $code): ?Subject;

    /** @return Subject[] */
    public function findAll(): array;

    public function save(Subject $subject): Subject;

    public function softDelete(int $id): void;

    public function updateOrCreateSubject(string $subjectCode, array $data): EloquentSubject;

    public function firstOrCreateDepartment(string $deptCode, array $data): EloquentDepartment;
}
