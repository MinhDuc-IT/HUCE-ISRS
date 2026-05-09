<?php

namespace App\Domain\Repositories;

use App\Models\Student;

interface StudentRepositoryPort
{
    public function updateOrCreate(string $studentCode, array $data): Student;
}
