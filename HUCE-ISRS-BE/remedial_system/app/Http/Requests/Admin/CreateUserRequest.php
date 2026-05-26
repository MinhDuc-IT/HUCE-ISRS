<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Models\User;

class CreateUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:6'],
            'role'          => ['required', 'string', 'in:' . implode(',', [User::ROLE_ADMIN, User::ROLE_BO_MON, User::ROLE_SINH_VIEN])],
            'student_code'  => ['required_if:role,' . User::ROLE_SINH_VIEN, 'nullable', 'string', 'max:20', 'unique:users,student_code'],
            'department_id' => ['required_if:role,' . User::ROLE_BO_MON, 'nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Họ tên không được để trống.',
            'email.required'            => 'Email không được để trống.',
            'email.email'               => 'Email không đúng định dạng.',
            'email.unique'              => 'Email đã tồn tại trong hệ thống.',
            'password.required'         => 'Mật khẩu không được để trống.',
            'password.min'              => 'Mật khẩu phải có ít nhất :min ký tự.',
            'role.required'             => 'Vai trò không được để trống.',
            'role.in'                   => 'Vai trò không hợp lệ.',
            'student_code.required_if'  => 'Mã sinh viên không được để trống khi vai trò là Sinh viên.',
            'student_code.unique'       => 'Mã sinh viên đã tồn tại trong hệ thống.',
            'department_id.required_if' => 'Bộ môn/Khoa không được để trống khi vai trò là Bộ môn.',
        ];
    }
}
