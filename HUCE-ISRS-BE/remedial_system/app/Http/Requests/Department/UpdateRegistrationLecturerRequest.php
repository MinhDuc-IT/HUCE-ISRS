<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ApiFormRequest;

class UpdateRegistrationLecturerRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBoMon() ?? false;
    }

    public function rules(): array
    {
        return [
            'teacher_id'            => ['required_without_all:lecture_name,lecturer_phone_number,lecturer_email', 'nullable', 'integer', 'exists:teachers,id'],
            'lecture_name'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'lecturer_phone_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'lecturer_email'        => ['sometimes', 'nullable', 'email', 'max:255'],
        ];
    }
}
