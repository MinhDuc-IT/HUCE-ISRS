<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryPort
{
    public function findByStudentCode(string $studentCode): ?User
    {
        return User::where('student_code', $studentCode)
            ->where('role', User::ROLE_SINH_VIEN)
            ->where('is_deleted', false)
            ->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->where('is_deleted', false)->first();
    }

    public function findStaffByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_BO_MON])
            ->where('is_deleted', false)
            ->first();
    }

    public function isStaffEmailDeactivated(string $email): bool
    {
        return User::withoutGlobalScopes()
            ->where('email', $email)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_BO_MON])
            ->where('is_deleted', true)
            ->exists();
    }

    public function isStudentCodeDeactivated(string $studentCode): bool
    {
        return User::withoutGlobalScopes()
            ->where('student_code', $studentCode)
            ->where('role', User::ROLE_SINH_VIEN)
            ->where('is_deleted', true)
            ->exists();
    }

    public function findById(int $id): ?User
    {
        return User::where('is_deleted', false)->find($id);
    }

    public function findAll(?string $role = null): array
    {
        $query = User::where('is_deleted', false)->orderByDesc('created_at');

        if ($role !== null && $role !== '') {
            $query->where('role', $role);
        }

        return $query->get()->all();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(int $id): void
    {
        User::withoutGlobalScopes()->whereKey($id)->update(['is_deleted' => true]);
    }

    public function findBoMonByDepartmentId(int $departmentId): ?User
    {
        return User::where('department_id', $departmentId)
            ->where('role', User::ROLE_BO_MON)
            ->where('is_deleted', false)
            ->first();
    }

    public function findAllBoMonUsers(): array
    {
        return User::where('role', User::ROLE_BO_MON)
            ->where('is_deleted', false)
            ->whereNotNull('department_id')
            ->get()
            ->all();
    }
}
