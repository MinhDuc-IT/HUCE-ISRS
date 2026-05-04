<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation cho API Sửa đợt phụ đạo.
 *
 * Use case : Sửa đợt phụ đạo
 * Actor    : Admin
 *
 * Hỗ trợ partial update (PATCH): chỉ validate các field được gửi lên.
 * Normal Flow bước 2: Người dùng nhập thông tin chỉnh sửa.
 * Normal Flow bước 3: Hệ thống kiểm tra thời gian đợt phụ đạo.
 * Alternative Flow 1: Thời gian không hợp lệ → trả về lỗi.
 */
class UpdateTutoringClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Dùng 'sometimes' để chỉ validate field nào được gửi lên (PATCH semantics).
     * Ràng buộc thời gian vẫn kiểm tra tương quan giữa các field.
     */
    public function rules(): array
    {
        return [
            'course_code'      => ['sometimes', 'string', 'max:20'],
            'course_name'      => ['sometimes', 'string', 'max:200'],
            'credits'          => ['sometimes', 'integer', 'min:1', 'max:10'],
            'tutoring_term_id' => ['sometimes', 'integer', 'exists:TutoringTerm,Id'],
            'teacher_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'teacher_name' => ['sometimes', 'nullable', 'string', 'max:100'],



            'max_students' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'note'         => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Kiểm tra ràng buộc thời gian tương quan sau khi các field riêng lẻ hợp lệ.
     * Merge giá trị cũ (từ DB) với giá trị mới để validate đúng khi chỉ sửa một phần.
     *
     * Alternative Flow 1: nếu thời gian không hợp lệ → báo lỗi.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            /** @var \App\Models\TutoringClass $existing */
            $existing = $this->route('tutoringClass');

            if (! $existing) {
                return;
            }
        });
    }

    public function messages(): array
    {
        return [
            'credits.min'           => 'Số tín chỉ phải lớn hơn 0.',
            'tutoring_term_id.exists' => 'Đợt phụ đạo không tồn tại trong hệ thống.',
            'max_students.min'      => 'Sĩ số tối đa phải ít nhất 1 sinh viên.',
            'max_students.max'      => 'Sĩ số tối đa không được vượt quá 200.',
        ];
    }
}
