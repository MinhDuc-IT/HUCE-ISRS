<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation cho API Cài đặt hệ thống.
 *
 * Use case: Cài đặt hệ thống
 * Actor: Admin
 */
class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'settings'         => ['required', 'array', 'min:1'],
            'settings.*.key'   => ['required', 'string', 'max:255'],
            'settings.*.value' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required'         => 'Danh sách cấu hình không được để trống.',
            'settings.array'            => 'Danh sách cấu hình phải là mảng.',
            'settings.*.key.required'   => 'Tên cấu hình không được để trống.',
        ];
    }
}
