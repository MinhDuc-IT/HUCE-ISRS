<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class UpdateDepartmentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('Phone') && ! $this->has('phone_number')) {
            $this->merge(['phone_number' => $this->Phone]);
        }
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'faculty_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'login_user'   => ['sometimes', 'array'],
            'login_user.name' => ['required_with:login_user', 'string', 'max:255'],
            'login_user.email'=> ['required_with:login_user', 'email', 'max:255'],
            'login_user.password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
