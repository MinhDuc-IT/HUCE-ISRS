<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'student_code'  => $this->student_code,
            'department_id' => $this->department_id,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
