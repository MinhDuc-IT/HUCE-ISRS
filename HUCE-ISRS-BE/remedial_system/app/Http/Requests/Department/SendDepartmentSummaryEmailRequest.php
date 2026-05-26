<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ApiFormRequest;

class SendDepartmentSummaryEmailRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBoMon() ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:500'],
            'body'    => ['nullable', 'string'],
        ];
    }
}
