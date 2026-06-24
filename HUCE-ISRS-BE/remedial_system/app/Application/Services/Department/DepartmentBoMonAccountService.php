<?php

namespace App\Application\Services\Department;

use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DepartmentBoMonAccountService
{
    public function __construct(
        private readonly UserRepositoryPort $userRepository,
    ) {}

    public function findByDepartmentId(int $departmentId): ?User
    {
        return $this->userRepository->findBoMonByDepartmentId($departmentId);
    }

    /** @return array<int, User> department_id => User */
    public function findAllIndexedByDepartmentId(): array
    {
        $indexed = [];

        foreach ($this->userRepository->findAllBoMonUsers() as $user) {
            if ($user->department_id !== null) {
                $indexed[(int) $user->department_id] = $user;
            }
        }

        return $indexed;
    }

    public function upsertForDepartment(int $departmentId, array $data, bool $passwordRequired = false): User
    {
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = isset($data['password']) ? trim((string) $data['password']) : '';

        if ($name === '' || $email === '') {
            throw new \DomainException('Thông tin tài khoản đăng nhập bộ môn không đầy đủ.');
        }

        $existing = $this->userRepository->findBoMonByDepartmentId($departmentId);

        if ($existing !== null) {
            $this->assertEmailAvailable($email, $existing->id);

            $updateData = [
                'name'  => $name,
                'email' => $email,
            ];

            if ($password !== '') {
                $updateData['password'] = Hash::make($password);
            }

            return $this->userRepository->update($existing, $updateData);
        }

        if ($passwordRequired && $password === '') {
            throw new \DomainException('Mật khẩu tài khoản bộ môn không được để trống.');
        }

        if ($password === '') {
            throw new \DomainException('Mật khẩu tài khoản bộ môn không được để trống.');
        }

        $this->assertEmailAvailable($email);

        return $this->userRepository->create([
            'name'          => $name,
            'email'         => $email,
            'password'      => Hash::make($password),
            'role'          => User::ROLE_BO_MON,
            'student_code'  => null,
            'department_id' => $departmentId,
            'is_deleted'    => false,
        ]);
    }

    public function updateLoginUser(User $user, array $data): User
    {
        $updateData = [];

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new \DomainException('Họ tên tài khoản không được để trống.');
            }
            $updateData['name'] = $name;
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '') {
                throw new \DomainException('Email đăng nhập không được để trống.');
            }
            $this->assertEmailAvailable($email, $user->id);
            $updateData['email'] = $email;
        }

        if (array_key_exists('password', $data)) {
            $password = trim((string) $data['password']);
            if ($password !== '') {
                if (strlen($password) < 6) {
                    throw new \DomainException('Mật khẩu phải có ít nhất 6 ký tự.');
                }
                $updateData['password'] = Hash::make($password);
            }
        }

        if ($updateData === []) {
            return $user;
        }

        return $this->userRepository->update($user, $updateData);
    }

    /** @return array{id: int, name: string, email: string}|null */
    public function toLoginUserPayload(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    private function assertEmailAvailable(string $email, ?int $ignoreUserId = null): void
    {
        $existing = $this->userRepository->findByEmail($email);

        if ($existing !== null && $existing->id !== $ignoreUserId) {
            throw new \DomainException('Email đăng nhập đã được sử dụng.');
        }
    }
}
