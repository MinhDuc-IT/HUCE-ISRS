<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ports\Persistence\TeacherRepositoryPort;
use App\Models\Teacher;

class EloquentTeacherRepository implements TeacherRepositoryPort
{
    public function updateOrCreateByEmail(string $email, array $data): Teacher
    {
        $attributes = [ 'email' => $email ];

        $values = [
            'department_id' => $data['department_id'] ?? null,
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
            'phone'         => $data['phone'] ?? null,
        ];

        return Teacher::updateOrCreate($attributes, array_filter($values, fn($v) => $v !== null));
    }
}
