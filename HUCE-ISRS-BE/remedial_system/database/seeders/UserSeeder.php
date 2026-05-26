<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed tài khoản mẫu cho Admin và Bộ môn.
 *
 * Sinh viên KHÔNG cần seed thủ công – tài khoản tự động được tạo
 * khi đăng nhập lần đầu qua University System (Option B – auto-provision).
 *
 * Mật khẩu mặc định cho sinh viên = student_code (VD: SV001)
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@remedial.edu.vn'],
            [
                'name'          => 'Quản trị hệ thống',
                'password'      => Hash::make('Admin@2024!'),
                'role'          => User::ROLE_ADMIN,
                'student_code'  => null,
                'department_id' => null,
                'is_deleted'    => false,
            ]
        );

        // ── Bộ môn mẫu: Công nghệ phần mềm (HUCE IDBoMon = 54) ─────────────────
        $departmentId = \App\Models\Department::where('department_code', '54')->value('id');

        User::updateOrCreate(
            ['email' => 'bokhoa.cntt@remedial.edu.vn'],
            [
                'name'          => 'Trần Thị Bộ Môn',
                'password'      => Hash::make('BoMon@2024!'),
                'role'          => User::ROLE_BO_MON,
                'student_code'  => null,
                'department_id' => $departmentId,
                'is_deleted'    => false,
            ]
        );

        $this->command->info('✅ UserSeeder hoàn tất.');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email / Mã SV', 'Password', 'Ghi chú'],
            [
                [User::ROLE_ADMIN,  'admin@remedial.edu.vn',       'Admin@2024!',  'Tạo thủ công'],
                [User::ROLE_BO_MON, 'bokhoa.cntt@remedial.edu.vn', 'BoMon@2024!',  'Tạo thủ công'],
                [User::ROLE_SINH_VIEN, 'student_code (VD: SV001)', '<student_code>', 'Auto-provision lần đầu login'],
            ]
        );
    }
}
