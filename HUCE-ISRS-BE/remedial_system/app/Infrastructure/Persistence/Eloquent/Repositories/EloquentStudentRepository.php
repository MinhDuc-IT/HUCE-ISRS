<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ports\Persistence\StudentRepositoryPort;
use App\Models\Student;

class EloquentStudentRepository implements StudentRepositoryPort
{
    public function updateOrCreate(string $studentCode, array $data): Student
    {
        return Student::updateOrCreate(
            ['student_code' => $studentCode],
            $data
        );
    }
}
