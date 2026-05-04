<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation cho API Thêm đợt phụ đạo.
 *
 * Use case : Thêm đợt phụ đạo
 * Actor    : Admin
 *
 * Normal Flow bước 2: Người dùng nhập thông tin đợt phụ đạo.
 * Normal Flow bước 3: Hệ thống kiểm tra thời gian đợt phụ đạo.
 * Alternative Flow 1: Thời gian không hợp lệ → trả về lỗi.
 */
class CreateTutoringClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Chỉ Admin mới được tạo đợt phụ đạo
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Quy tắc validation – tương ứng bước 2 + 3 Normal Flow.
     */
    public function rules(): array
    {
        return [
            // Thông tin môn học (lấy từ University System hoặc nhập tay)
            'course_code'  => ['required', 'string', 'max:20'],
            'course_name'  => ['required', 'string', 'max:200'],
            'credits'      => ['required', 'integer', 'min:1', 'max:10'],

            // Học kỳ
            'semester_id'  => ['required', 'integer', 'exists:semesters,id'],

            // Giảng viên (tuỳ chọn, có thể gán sau)
            'teacher_code' => ['nullable', 'string', 'max:20'],
            'teacher_name' => ['nullable', 'string', 'max:100'],

            // Thời gian – bước 3 Normal Flow
            'start_date'            => ['required', 'date', 'after_or_equal:today'],
            'end_date'              => ['required', 'date', 'after:start_date'],
            'registration_deadline' => [
                'required',
                'date',
                'before:start_date',      // hạn đăng ký phải trước ngày bắt đầu
            ],

            // Sĩ số
            'max_students' => ['required', 'integer', 'min:1', 'max:200'],

            // Ghi chú
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt.
     * Alternative Flow 1: Nếu thời gian không hợp lệ → hiển thị thông báo này.
     */
    public function messages(): array
    {
        return [
            'course_code.required'  => 'Mã môn học không được để trống.',
            'course_name.required'  => 'Tên môn học không được để trống.',
            'credits.required'      => 'Số tín chỉ không được để trống.',
            'credits.min'           => 'Số tín chỉ phải lớn hơn 0.',
            'semester_id.required'  => 'Vui lòng chọn học kỳ.',
            'semester_id.exists'    => 'Học kỳ không tồn tại trong hệ thống.',

            // Thời gian – AF-1
            'start_date.required'              => 'Ngày bắt đầu không được để trống.',
            'start_date.after_or_equal'        => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.required'                => 'Ngày kết thúc không được để trống.',
            'end_date.after'                   => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'registration_deadline.required'   => 'Hạn đăng ký không được để trống.',
            'registration_deadline.before'     => 'Hạn đăng ký phải trước ngày bắt đầu đợt phụ đạo.',

            'max_students.required' => 'Sĩ số tối đa không được để trống.',
            'max_students.min'      => 'Sĩ số tối đa phải ít nhất 1 sinh viên.',
            'max_students.max'      => 'Sĩ số tối đa không được vượt quá 200.',
        ];
    }
}
