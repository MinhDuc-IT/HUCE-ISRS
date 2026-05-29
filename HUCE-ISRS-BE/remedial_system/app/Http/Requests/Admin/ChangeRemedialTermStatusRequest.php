<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class ChangeRemedialTermStatusRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', 'in:0,1,2,3,4'],
            // 'action' => ['required', 'string', 'in:openRegistration,activate,complete,cancel'], // legacy
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái không được để trống.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ];
    }
}
