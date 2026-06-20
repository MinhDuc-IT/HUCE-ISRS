<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'key'         => 'sender_email',
                'value'       => 'byebyevidu@gmail.com',
                'description' => 'Email dùng để gửi email hệ thống',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'sender_password',
                'value'       => '',
                'description' => 'Mật khẩu email gửi',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'admin_email',
                'value'       => 'phongdaotao@nuce.edu.vn',
                'description' => 'Email đơn vị quản lý',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'weeks_from_registration',
                'value'       => '10',
                'description' => 'Số tuần tính từ tuần đăng ký',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'ws_login',
                'value'       => 'http://127.0.0.1:8001/api/student/login',
                'description' => 'Webservice đăng nhập',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'ws_student_info',
                'value'       => 'http://127.0.0.1:8001',
                'description' => 'Webservice lấy thông tin sinh viên',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'ws_host',
                'value'       => 'http://127.0.0.1:8001',
                'description' => 'Host webservice học phụ đạo',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'default_periods',
                'value'       => '45',
                'description' => 'Số tiết học mặc định khi đăng ký phụ đạo',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'mail_summary_subject',
                'value'       => 'Danh sách sinh viên đăng ký học phụ đạo',
                'description' => 'Tiêu đề email gửi tóm tắt cho bộ môn',
                'is_deleted'  => false,
            ],
            [
                'key'         => 'mail_summary_body',
                'value'       => 'Kính gửi Bộ môn, đây là danh sách chi tiết các môn học và sinh viên đã đăng ký học phụ đạo trong đợt này.',
                'description' => 'Nội dung email gửi tóm tắt cho bộ môn',
                'is_deleted'  => false,
            ],
        ];

        foreach ($configs as $config) {
            DB::table('system_configurations')->updateOrInsert(
                ['key' => $config['key']],
                array_merge($config, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command?->info('✅ SystemConfigSeeder hoàn tất.');
    }
}
