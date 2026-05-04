<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'Key' => 'min_students_per_class',
                'Value' => '10',
                'Description' => 'Sĩ số tối thiểu để mở lớp học bổ sung',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
            [
                'Key' => 'max_students_per_class',
                'Value' => '30',
                'Description' => 'Sĩ số tối đa mặc định cho lớp học bổ sung',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
            [
                'Key' => 'default_periods',
                'Value' => '45',
                'Description' => 'Số tiết học mặc định',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
            [
                'Key' => 'mail_summary_subject',
                'Value' => 'Danh sách sinh viên đăng ký học phụ đạo',
                'Description' => 'Tiêu đề email gửi tóm tắt cho bộ môn',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
            [
                'Key' => 'mail_summary_body',
                'Value' => 'Kính gửi Bộ môn, đây là danh sách chi tiết các môn học và sinh viên đã đăng ký học phụ đạo trong đợt này.',
                'Description' => 'Nội dung email gửi tóm tắt cho bộ môn',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
        ];

        foreach ($configs as $config) {
            DB::table('SystemConfig')->updateOrInsert(
                ['Key' => $config['Key']],
                $config
            );
        }
    }
}
