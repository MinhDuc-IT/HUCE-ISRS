<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation cho API Sửa người dùng.
 *
 * Use case: Sửa người dùng
 * Actor: Admin
 *
 * Normal Flow bước 2: Người dùng nhập thông tin chỉnh sửa (PATCH).
 * Normal Flow bước 3: Hệ thống kiểm tra tính hợp lệ của thông tin.
 * Alternative Flow 1: Thông tin không hợp lệ → báo lỗi.
 */
class UpdateUserRequest extends FormRequest
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

    /**
     * Ràng buộc dữ liệu phụ thuộc vào Role (merge request và DB)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            /** @var \App\Models\User $existing */
            $existing = $this->route('user');
            if (! $existing) {
                return;
            }

            // Lấy giá trị mới hoặc fallback về giá trị hiện tại
            $role         = $this->input('role', $existing->role);
            
            // Đối với field có thể set null, chúng ta cần dùng array_key_exists để biết request có truyền lên không
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
            'name.required'             => 'Họ tên không được để trống.',
            'email.required'            => 'Email không được để trống.',
            'email.email'               => 'Email không đúng định dạng.',
            'email.unique'              => 'Email đã tồn tại trong hệ thống.',
            'password.required'         => 'Mật khẩu không được để trống.',
            'password.min'              => 'Mật khẩu phải có ít nhất :min ký tự.',
            'role.required'             => 'Vai trò không được để trống.',
            'role.in'                   => 'Vai trò không hợp lệ.',
            'student_code.unique'       => 'Mã sinh viên đã tồn tại trong hệ thống.',
        ];
    }
}
