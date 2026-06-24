<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class StoreDepartmentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('DepartmentCode') && ! $this->has('department_code')) {
            $merge['department_code'] = $this->DepartmentCode;
        }
        if ($this->has('Name') && ! $this->has('name')) {
            $merge['name'] = $this->Name;
        }
        if ($this->has('Email') && ! $this->has('email')) {
            $merge['email'] = $this->Email;
        }
        if ($this->has('Phone') && ! $this->has('phone_number')) {
            $merge['phone_number'] = $this->Phone;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'department_code' => ['required', 'string', 'max:50', 'unique:departments,department_code'],
            'faculty_code'    => ['nullable', 'string', 'max:50'],
            'faculty_name'    => ['nullable', 'string', 'max:255'],
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone_number'    => ['nullable', 'string', 'max:50'],
            'login_user'      => ['required', 'array'],
            'login_user.name' => ['required', 'string', 'max:255'],
            'login_user.email'=> ['required', 'email', 'max:255', 'unique:users,email'],
            'login_user.password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_code.required' => 'Mã bộ môn không được để trống.',
            'department_code.unique' => 'Mã bộ môn đã tồn tại.',
            'name.required'          => 'Tên bộ môn không được để trống.',
        ];
    }
}
