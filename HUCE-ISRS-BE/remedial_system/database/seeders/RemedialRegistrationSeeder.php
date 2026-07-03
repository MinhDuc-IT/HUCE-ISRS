<?php

namespace Database\Seeders;

use App\Models\RemedialRegistration;
use App\Models\RemedialTerm;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed đăng ký phụ đạo MẪU để test luồng Phân công giảng viên.
 *
 * Gắn vào đợt "HK1 Block 2 (2023-2024)" (year=2023, semester=2):
 * cửa đăng ký đã đóng (4/12/2023) nhưng đợt còn ACTIVE → can_assign_lecturer = true.
 *
 * Chỉ đăng ký các môn thuộc bộ môn CNTT (department_code 54) để khớp
 * tài khoản bộ môn mẫu bokhoa.cntt@remedial.edu.vn (department_id = 54).
 */
class RemedialRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $term = RemedialTerm::where('year', 2023)->where('semester', 2)->first();

        if ($term === null) {
            $this->command?->warn('⚠️  Bỏ qua: chưa có đợt HK1 Block 2 (2023-2024). Chạy RemedialTermSeeder trước.');
            return;
        }

        // Sinh viên mẫu.
        $students = [
            ['student_code' => 'SV2023001', 'full_name' => 'Nguyễn Văn An',  'email' => 'sv2023001@huce.edu.vn'],
            ['student_code' => 'SV2023002', 'full_name' => 'Trần Thị Bình',  'email' => 'sv2023002@huce.edu.vn'],
            ['student_code' => 'SV2023003', 'full_name' => 'Lê Hoàng Cường', 'email' => 'sv2023003@huce.edu.vn'],
            ['student_code' => 'SV2023004', 'full_name' => 'Phạm Thị Dung',  'email' => 'sv2023004@huce.edu.vn'],
        ];

        $studentIds = [];
        foreach ($students as $s) {
            // Bảng students (đồng bộ tham khảo).
            Student::firstOrCreate(
                ['student_code' => $s['student_code']],
                ['full_name' => $s['full_name'], 'email' => $s['email'], 'is_deleted' => false],
            );

            // FK remedial_registrations.student_id -> users.id, nên cần user role sinh_vien.
            $user = User::firstOrCreate(
                ['student_code' => $s['student_code']],
                [
                    'name'       => $s['full_name'],
                    'email'      => $s['email'],
                    'password'   => Hash::make($s['student_code']),
                    'role'       => 'sinh_vien',
                    'is_deleted' => false,
                ],
            );
            $studentIds[$s['student_code']] = $user->id;
        }

        // Đăng ký: [student_code, subject_code, số tiết]. Môn thuộc bộ môn 54.
        $registrations = [
            ['SV2023001', 'INT101', 45],
            ['SV2023002', 'INT101', 45],
            ['SV2023003', 'INT101', 45],
            ['SV2023001', 'INT102', 30],
            ['SV2023004', 'INT102', 30],
            ['SV2023002', 'INT201', 45],
            ['SV2023003', 'INT201', 45],
        ];

        $count = 0;
        $skipped = 0;
        foreach ($registrations as [$studentCode, $subjectCode, $periods]) {
            $subject = Subject::where('subject_code', $subjectCode)->first();
            $studentId = $studentIds[$studentCode] ?? null;

            if ($subject === null || $studentId === null) {
                $skipped++;
                continue;
            }

            RemedialRegistration::firstOrCreate(
                [
                    'student_id'       => $studentId,
                    'remedial_term_id' => $term->id,
                    'subject_id'       => $subject->id,
                ],
                [
                    'remedial_periods'  => $periods,
                    'registration_date' => '2023-11-15 09:00:00',
                    'is_deleted'        => false,
                ],
            );
            $count++;
        }

        $this->command?->info(
            "✅ RemedialRegistrationSeeder: {$count} đăng ký" . ($skipped ? ", bỏ qua {$skipped}." : '.')
        );
    }
}
