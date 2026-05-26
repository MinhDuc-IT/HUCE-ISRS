<?php

namespace App\Application\Services\Admin;

use App\Application\Services\Department\SendDepartmentSummaryEmailService;
use App\Domain\Entities\Department;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;

class ManageDepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryPort $departmentRepository,
        private readonly SendDepartmentSummaryEmailService $summaryEmailService,
    ) {}

    /** @return Department[] */
    public function list(): array
    {
        return $this->departmentRepository->findAll();
    }

    public function findById(int $id): ?Department
    {
        return $this->departmentRepository->findById($id);
    }

    public function create(array $data): Department
    {
        $code = trim((string) $data['department_code']);

        if ($this->departmentRepository->findByCode($code) !== null) {
            throw new \DomainException('Mã bộ môn đã tồn tại.');
        }

        $entity = new Department(
            id:             null,
            departmentCode: $code,
            departmentName: trim($data['name']),
            facultyCode:    isset($data['faculty_code']) ? trim((string) $data['faculty_code']) : $code,
            facultyName:    isset($data['faculty_name']) ? trim((string) $data['faculty_name']) : null,
            email:          $data['email'] ?? null,
            phoneNumber:    $data['phone_number'] ?? null,
        );

        return $this->departmentRepository->save($entity);
    }

    public function update(int $id, array $data): Department
    {
        $existing = $this->requireById($id);

        $entity = new Department(
            id:             $id,
            departmentCode: $existing->departmentCode,
            departmentName: $data['name'] ?? $existing->departmentName,
            facultyCode:    $existing->facultyCode,
            facultyName:    array_key_exists('faculty_name', $data)
                ? trim((string) $data['faculty_name'])
                : $existing->facultyName,
            email:          array_key_exists('email', $data) ? $data['email'] : $existing->email,
            phoneNumber:    array_key_exists('phone_number', $data) ? $data['phone_number'] : $existing->phoneNumber,
            createdAt:      $existing->createdAt,
        );

        return $this->departmentRepository->save($entity);
    }

    public function delete(int $id): void
    {
        $this->requireById($id);
        $this->departmentRepository->softDelete($id);
    }

    public function sendSummaryEmail(int $id, ?string $subject = null, ?string $body = null): void
    {
        $this->summaryEmailService->send($id, $subject, $body);
    }

    private function requireById(int $id): Department
    {
        $dept = $this->departmentRepository->findById($id);

        if ($dept === null) {
            throw new \DomainException('Bộ môn không tồn tại');
        }

        return $dept;
    }
}
