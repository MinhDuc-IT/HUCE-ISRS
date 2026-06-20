<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;

class StoreRemedialTermRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'year'                 => ['required', 'integer', 'min:2000', 'max:2100'],
            'semester'             => ['required', 'integer', 'min:1', 'max:3'],
            'name'                 => ['required', 'string', 'max:255'],
            'start_date'           => ['required', 'date'],
            'end_date'             => ['required', 'date', 'after_or_equal:start_date'],
            'registration_start'   => ['required', 'date'],
            'registration_end'     => ['required', 'date', 'after_or_equal:registration_start'],
            'remedial_coefficient' => ['nullable', 'integer', 'min:0'],
            'price_per_period'     => ['nullable', 'integer', 'min:0'],
            'price_coefficient'    => ['nullable', 'numeric', 'min:0'],
            // 'is_current_term'      => ['sometimes', 'boolean'], // legacy, derived from status.
        ];
    }

    public function messages(): array
    {
        return [
            'year.required'                    => 'Năm học không được để trống.',
            'semester.required'                => 'Học kỳ không được để trống.',
            'name.required'                    => 'Tên đợt phụ đạo không được để trống.',
            'start_date.required'              => 'Ngày bắt đầu không được để trống.',
            'end_date.required'                => 'Ngày kết thúc không được để trống.',
            'end_date.after_or_equal'          => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'registration_start.required'      => 'Ngày mở đăng ký không được để trống.',
            'registration_end.required'        => 'Ngày đóng đăng ký không được để trống.',
            'registration_end.after_or_equal'  => 'Ngày đóng đăng ký phải sau hoặc bằng ngày mở đăng ký.',
        ];
    }
}
