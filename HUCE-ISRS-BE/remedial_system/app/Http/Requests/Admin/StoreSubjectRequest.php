<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class StoreSubjectRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'subject_code'  => ['required', 'string', 'max:50', 'unique:subjects,subject_code'],
            'name'          => ['required', 'string', 'max:255'],
            'credits'       => ['nullable', 'integer', 'min:0'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_code.required'  => 'Mã môn học không được để trống.',
            'subject_code.unique'    => 'Mã môn học đã tồn tại.',
            'name.required'          => 'Tên môn học không được để trống.',
            'department_id.required' => 'Bộ môn không được để trống.',
        ];
    }
}
