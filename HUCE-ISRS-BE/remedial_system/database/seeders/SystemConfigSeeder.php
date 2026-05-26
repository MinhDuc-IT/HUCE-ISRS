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
