<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Models\User;

class UpdateUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'email'         => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'      => ['sometimes', 'required', 'string', 'min:6'],
            'role'          => ['sometimes', 'required', 'string', 'in:' . implode(',', [User::ROLE_ADMIN, User::ROLE_BO_MON, User::ROLE_SINH_VIEN])],
            'student_code'  => ['sometimes', 'nullable', 'string', 'max:20', 'unique:users,student_code,' . $userId],
            'department_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $existing = $this->route('user');
            if (! $existing) {
                return;
            }

            $role         = $this->input('role', $existing->role);
            $studentCode  = $this->has('student_code') ? $this->input('student_code') : $existing->student_code;
            $departmentId = $this->has('department_id') ? $this->input('department_id') : $existing->department_id;

            if ($role === User::ROLE_SINH_VIEN && empty($studentCode)) {
                $v->errors()->add('student_code', 'Mã sinh viên không được để trống khi vai trò là Sinh viên.');
            }

            if ($role === User::ROLE_BO_MON && empty($departmentId)) {
                $v->errors()->add('department_id', 'Bộ môn/Khoa không được để trống khi vai trò là Bộ môn.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Họ tên không được để trống.',
            'email.required'      => 'Email không được để trống.',
            'email.email'         => 'Email không đúng định dạng.',
            'email.unique'        => 'Email đã tồn tại trong hệ thống.',
            'password.required'   => 'Mật khẩu không được để trống.',
            'password.min'        => 'Mật khẩu phải có ít nhất :min ký tự.',
            'role.required'       => 'Vai trò không được để trống.',
            'role.in'             => 'Vai trò không hợp lệ.',
            'student_code.unique' => 'Mã sinh viên đã tồn tại trong hệ thống.',
        ];
    }
}
