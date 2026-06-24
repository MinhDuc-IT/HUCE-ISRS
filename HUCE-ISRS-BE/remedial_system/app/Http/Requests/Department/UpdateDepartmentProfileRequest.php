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
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'login_user'   => ['sometimes', 'array'],
            'login_user.name' => ['sometimes', 'string', 'max:255'],
            'login_user.email'=> ['sometimes', 'email', 'max:255'],
            'login_user.password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
