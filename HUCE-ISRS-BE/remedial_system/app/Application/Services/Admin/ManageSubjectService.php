<?php

namespace App\Application\Services\Admin;

use App\Domain\Entities\Subject;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;

class ManageSubjectService
{
    public function __construct(
        private readonly SubjectRepositoryPort $subjectRepository,
    ) {}

    /** @return Subject[] */
    public function list(): array
    {
        return $this->subjectRepository->findAll();
    }

    public function findById(int $id): ?Subject
    {
        return $this->subjectRepository->findById($id);
    }

    public function create(array $data): Subject
    {
        $code = strtoupper(trim($data['subject_code']));

        if ($this->subjectRepository->findByCode($code) !== null) {
            throw new \DomainException('Mã môn học đã tồn tại.');
        }

        $entity = new Subject(
            id:           null,
            subjectCode:  $code,
            name:         trim($data['name']),
            credits:      isset($data['credits']) ? (int) $data['credits'] : null,
            departmentId: (int) $data['department_id'],
        );

        return $this->subjectRepository->save($entity);
    }

    public function update(int $id, array $data): Subject
    {
        $existing = $this->requireById($id);

        $entity = new Subject(
            id:           $id,
            subjectCode:  $existing->subjectCode,
            name:         $data['name'] ?? $existing->name,
            credits:      array_key_exists('credits', $data) ? (int) $data['credits'] : $existing->credits,
            departmentId: isset($data['department_id']) ? (int) $data['department_id'] : $existing->departmentId,
        );

        return $this->subjectRepository->save($entity);
    }

    public function delete(int $id): void
    {
        $this->requireById($id);
        $this->subjectRepository->softDelete($id);
    }

    private function requireById(int $id): Subject
    {
        $subject = $this->subjectRepository->findById($id);

        if ($subject === null) {
            throw new \DomainException('Môn học không tồn tại');
        }

        return $subject;
    }
}
