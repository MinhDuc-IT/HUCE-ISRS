<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class LoginRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'email.email'       => 'Email không đúng định dạng.',
            'student_code.max'  => 'Mã sinh viên không được vượt quá 20 ký tự.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min'      => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];
    }
}
