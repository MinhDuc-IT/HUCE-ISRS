<?php

namespace App\Domain\Ports\Persistence;

use App\Models\User;

interface UserRepositoryPort
{
    public function findByStudentCode(string $studentCode): ?User;

    public function findByEmail(string $email): ?User;

    public function findStaffByEmail(string $email): ?User;

    public function isStaffEmailDeactivated(string $email): bool;

    public function isStudentCodeDeactivated(string $studentCode): bool;

    public function findById(int $id): ?User;

    /** @return User[] */
    public function findAll(?string $role = null): array;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(int $id): void;
}
