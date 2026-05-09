<?php

namespace App\Domain\Repositories;

use App\Models\User;

interface UserRepositoryPort
{
    public function findByStudentCode(string $studentCode): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
}
