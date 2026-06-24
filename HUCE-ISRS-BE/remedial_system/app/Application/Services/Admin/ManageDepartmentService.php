<?php

namespace App\Application\Services\Admin;

use App\Application\Services\Department\DepartmentBoMonAccountService;
use App\Application\Services\Department\SendDepartmentSummaryEmailService;
use App\Domain\Entities\Department;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;

class ManageDepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryPort $departmentRepository,
        private readonly SendDepartmentSummaryEmailService $summaryEmailService,
        private readonly DepartmentBoMonAccountService $boMonAccountService,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function listWithLoginUsers(): array
    {
        $departments = $this->departmentRepository->findAll();
        $loginUsers  = $this->boMonAccountService->findAllIndexedByDepartmentId();

        return array_map(
            fn (Department $dept) => $this->formatDepartment($dept, $loginUsers[$dept->id] ?? null),
            $departments
        );
    }

    public function findByIdWithLoginUser(int $id): ?array
    {
        $dept = $this->departmentRepository->findById($id);

        if ($dept === null) {
            return null;
        }

        return $this->formatDepartment($dept, $this->boMonAccountService->findByDepartmentId($id));
    }

    /** @return Department[] */
    public function list(): array
    {
        return $this->departmentRepository->findAll();
    }

    public function findById(int $id): ?Department
    {
        return $this->departmentRepository->findById($id);
    }

    public function create(array $data): array
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

        $dept = $this->departmentRepository->save($entity);
        $loginUser = $this->boMonAccountService->upsertForDepartment(
            $dept->id,
            $data['login_user'],
            passwordRequired: true,
        );

        return $this->formatDepartment($dept, $loginUser);
    }

    public function update(int $id, array $data): array
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

        $dept = $this->departmentRepository->save($entity);
        $loginUser = $this->boMonAccountService->findByDepartmentId($id);

        if (isset($data['login_user']) && is_array($data['login_user'])) {
            $loginUser = $this->boMonAccountService->upsertForDepartment($id, $data['login_user']);
        }

        return $this->formatDepartment($dept, $loginUser);
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

    /** @return array<string, mixed> */
    private function formatDepartment(Department $dept, ?\App\Models\User $loginUser = null): array
    {
        $data = (new \App\Http\Resources\DepartmentResource($dept))->resolve();
        $data['login_user'] = $this->boMonAccountService->toLoginUserPayload($loginUser);

        return $data;
    }
}
