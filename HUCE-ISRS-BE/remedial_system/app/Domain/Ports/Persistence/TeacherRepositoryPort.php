<?php

namespace App\Domain\Ports\Persistence;

use App\Models\Teacher;

interface TeacherRepositoryPort
{
    /**
     * Update or create teacher by email.
     * @return Teacher
     */
    public function updateOrCreateByEmail(string $email, array $data): Teacher;
}
