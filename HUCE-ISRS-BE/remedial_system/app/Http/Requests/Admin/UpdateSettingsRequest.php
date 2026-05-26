<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class UpdateSettingsRequest extends ApiFormRequest
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
            'settings.required'       => 'Danh sách cấu hình không được để trống.',
            'settings.array'          => 'Danh sách cấu hình phải là mảng.',
            'settings.*.key.required' => 'Tên cấu hình không được để trống.',
        ];
    }
}
