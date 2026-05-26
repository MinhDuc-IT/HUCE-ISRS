<?php

namespace App\Domain\Ports\Persistence;

use App\Models\Student;

interface StudentRepositoryPort
{
    public function updateOrCreate(string $studentCode, array $data): Student;
}
