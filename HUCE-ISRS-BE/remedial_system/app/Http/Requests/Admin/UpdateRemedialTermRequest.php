<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class UpdateRemedialTermRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'year'                 => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'semester'             => ['sometimes', 'integer', 'min:1', 'max:3'],
            'name'                 => ['sometimes', 'string', 'max:255'],
            'start_date'           => ['sometimes', 'date'],
            'end_date'             => ['sometimes', 'date', 'after_or_equal:start_date'],
            'registration_start'   => ['sometimes', 'date'],
            'registration_end'     => ['sometimes', 'date', 'after_or_equal:registration_start'],
            'remedial_coefficient' => ['sometimes', 'integer', 'min:0'],
            'price_per_period'     => ['sometimes', 'integer', 'min:0'],
            'price_coefficient'    => ['sometimes', 'numeric', 'min:0'],
            'is_current_term'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal'         => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'registration_end.after_or_equal' => 'Ngày đóng đăng ký phải sau hoặc bằng ngày mở đăng ký.',
        ];
    }
}
