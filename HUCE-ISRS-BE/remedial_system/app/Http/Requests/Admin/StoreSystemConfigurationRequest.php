<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class StoreSystemConfigurationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'key'         => ['required', 'string', 'max:255'],
            'value'       => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Tên cấu hình không được để trống.',
            'key.max'      => 'Tên cấu hình không được vượt quá 255 ký tự.',
        ];
    }
}
