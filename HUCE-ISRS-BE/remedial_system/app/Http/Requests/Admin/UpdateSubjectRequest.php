<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class UpdateSubjectRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'credits'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
        ];
    }
}
