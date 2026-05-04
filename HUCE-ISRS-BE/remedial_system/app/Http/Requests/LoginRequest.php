<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation cho API đăng nhập.
 *
 * Use case: Đăng nhập
 * Actor: Admin, Bộ môn, Sinh viên
 *
 * - Admin / Bộ môn : đăng nhập bằng email + password
 * - Sinh viên      : đăng nhập bằng student_code + password
 *   → Nếu chưa có tài khoản local, hệ thống tự xác minh qua University System
 *     và tạo tài khoản tự động (Option B – auto-provision).
 *
 * Normal Flow bước 2: Kiểm tra tính hợp lệ thông tin đầu vào.
 * Alternative Flow 1: Trả về lỗi validation nếu thiếu / sai định dạng.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chấp nhận EITHER email (admin/bo_mon) OR student_code (sinh_vien).
     * Ít nhất một trong hai phải có mặt.
     */
    public function rules(): array
    {
        return [
            'email'        => ['nullable', 'string', 'email', 'max:255'],
            'student_code' => ['nullable', 'string', 'max:20'],
            'password'     => ['required', 'string', 'min:6'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->email) && empty($this->student_code)) {
                $v->errors()->add('credential', 'Vui lòng cung cấp email hoặc mã sinh viên.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.email'          => 'Email không đúng định dạng.',
            'student_code.max'     => 'Mã sinh viên không được vượt quá 20 ký tự.',
            'password.required'    => 'Mật khẩu không được để trống.',
            'password.min'         => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];
    }
}
