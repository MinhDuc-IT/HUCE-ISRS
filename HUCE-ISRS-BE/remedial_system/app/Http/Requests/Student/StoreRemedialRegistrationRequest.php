<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\ApiFormRequest;
use App\Models\User;

class StoreRemedialRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSinhVien() ?? false;
    }

    public function rules(): array
    {
        return [
            'course_code'       => ['required_without:course_codes', 'string', 'max:50'],
            'course_codes'      => ['required_without:course_code', 'array', 'min:1'],
            'course_codes.*'    => ['string', 'max:50'],
            'remedial_periods'  => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_code.required_without'  => 'Mã học phần không được để trống.',
            'course_codes.required_without' => 'Danh sách mã học phần không được để trống.',
        ];
    }
}
