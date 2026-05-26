<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ApiFormRequest;

class UpdateDepartmentProfileRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBoMon() ?? false;
    }

    public function rules(): array
    {
        return [
            'email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
