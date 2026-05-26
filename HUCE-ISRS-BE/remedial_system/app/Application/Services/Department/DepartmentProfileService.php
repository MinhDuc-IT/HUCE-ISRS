<?php

namespace App\Application\Services\Department;

use App\Domain\Entities\Department;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;
use App\Models\User;

class DepartmentProfileService
{
    public function __construct(
        private readonly DepartmentRepositoryPort $departmentRepository,
    ) {}

    public function getProfile(User $user): Department
    {
        return $this->requireDepartment($user);
    }

    public function updateProfile(User $user, array $data): Department
    {
        $existing = $this->requireDepartment($user);

        $entity = new Department(
            id:             $existing->id,
            departmentCode: $existing->departmentCode,
            departmentName: $existing->departmentName,
            facultyCode:    $existing->facultyCode,
            facultyName:    $existing->facultyName,
            email:          array_key_exists('email', $data) ? $data['email'] : $existing->email,
            phoneNumber:    array_key_exists('phone_number', $data) ? $data['phone_number'] : $existing->phoneNumber,
            createdAt:      $existing->createdAt,
        );

        return $this->departmentRepository->save($entity);
    }

    public function resolveDepartmentId(User $user): int
    {
        return $this->requireDepartment($user)->id;
    }

    private function requireDepartment(User $user): Department
    {
        $this->assertBoMon($user);

        if ($user->department_id === null) {
            throw new \DomainException('Tài khoản chưa được gắn bộ môn.');
        }

        $dept = $this->departmentRepository->findById((int) $user->department_id);

        if ($dept === null) {
            throw new \DomainException('Bộ môn không tồn tại hoặc đã bị vô hiệu hóa.');
        }

        return $dept;
    }

    private function assertBoMon(User $user): void
    {
        if (! $user->isBoMon()) {
            throw new \DomainException('Chỉ tài khoản bộ môn mới được thực hiện thao tác này.');
        }
    }
}
