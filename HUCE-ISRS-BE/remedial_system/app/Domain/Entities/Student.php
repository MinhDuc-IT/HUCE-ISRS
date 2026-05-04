<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Sinh Viên
 */
class Student
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $studentCode,
        public readonly string  $fullName,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?Carbon $dateOfBirth = null,
        public readonly ?string $gender = null,
        public readonly ?string $status = null,
        public readonly Carbon  $createdAt = new Carbon(),
        public readonly ?Carbon $updatedAt = null,
    ) {}
}
