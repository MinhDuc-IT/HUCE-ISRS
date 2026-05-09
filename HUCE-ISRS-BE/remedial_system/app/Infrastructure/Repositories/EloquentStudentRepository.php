<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\StudentRepositoryPort;
use App\Models\Student;

class EloquentStudentRepository implements StudentRepositoryPort
{
    public function updateOrCreate(string $studentCode, array $data): Student
    {
        return Student::updateOrCreate(
            ['StudentCode' => $studentCode],
            $data
        );
    }
}
