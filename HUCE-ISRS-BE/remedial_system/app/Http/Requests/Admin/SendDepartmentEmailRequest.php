<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class SendDepartmentEmailRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:500'],
            'body'    => ['nullable', 'string'],
        ];
    }
}
