<?php

namespace App\Application\Services\Admin;

use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManageUserService
{
    public function __construct(
        private readonly UserRepositoryPort $userRepository,
    ) {}

    /** @return User[] */
    public function list(?string $role = null): array
    {
        return $this->userRepository->findAll($role);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(array $data): User
    {
        $role = $data['role'];

        return $this->userRepository->create([
            'name'          => trim($data['name']),
            'email'         => trim($data['email']),
            'password'      => Hash::make($data['password']),
            'role'          => $role,
            'student_code'  => $role === User::ROLE_SINH_VIEN
                ? strtoupper(trim((string) $data['student_code']))
                : null,
            'department_id' => $role === User::ROLE_BO_MON
                ? (int) $data['department_id']
                : null,
            'is_deleted'    => false,
        ]);
    }

    public function update(User $user, array $data): User
    {
        $updateData = [];
        $targetRole = $data['role'] ?? $user->role;

        if (array_key_exists('name', $data)) {
            $updateData['name'] = trim($data['name']);
        }
        if (array_key_exists('email', $data)) {
            $updateData['email'] = trim($data['email']);
        }
        if (array_key_exists('password', $data)) {
            $updateData['password'] = Hash::make($data['password']);
        }
        if (array_key_exists('role', $data)) {
            $updateData['role'] = $data['role'];
        }

        if (array_key_exists('student_code', $data)) {
            $updateData['student_code'] = $targetRole === User::ROLE_SINH_VIEN
                ? strtoupper(trim((string) $data['student_code']))
                : null;
        } elseif (array_key_exists('role', $data) && $targetRole !== User::ROLE_SINH_VIEN) {
            $updateData['student_code'] = null;
        }

        if (array_key_exists('department_id', $data)) {
            $updateData['department_id'] = $targetRole === User::ROLE_BO_MON
                ? (int) $data['department_id']
                : null;
        } elseif (array_key_exists('role', $data) && $targetRole !== User::ROLE_BO_MON) {
            $updateData['department_id'] = null;
        }

        return $this->userRepository->update($user, $updateData);
    }

    public function delete(int $id, int $actingUserId): void
    {
        if ($id === $actingUserId) {
            throw new \DomainException('Không thể xóa tài khoản của chính mình.');
        }

        $user = $this->userRepository->findById($id);
        if ($user === null) {
            throw new \DomainException('Không tìm thấy người dùng.');
        }

        try {
            $this->userRepository->delete($id);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new \DomainException(
                    'Không thể xóa người dùng này vì đã có dữ liệu liên kết trong hệ thống.'
                );
            }

            throw $e;
        }
    }
}
