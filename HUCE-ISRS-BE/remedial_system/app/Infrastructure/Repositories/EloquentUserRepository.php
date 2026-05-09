<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\UserRepositoryPort;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryPort
{
    public function findByStudentCode(string $studentCode): ?User
    {
        return User::where('student_code', $studentCode)
            ->where('role', User::ROLE_SINH_VIEN)
            ->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
