<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder tạo danh sách API clients cho luồng xác thực machine-to-machine.
 *
 * Chạy một lần sau khi migrate bảng api_clients.
 * client_secret phải được hash bằng bcrypt trước khi lưu.
 */
class ApiClientSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Remedial Registration System ────────────────────────────────────
        ApiClient::updateOrCreate(
            ['client_id' => 'remedial_system'],
            [
                'client_secret' => Hash::make('remedial_secret_2024'),
                'name'          => 'Hệ thống Đăng ký Học phần Bổ sung',
                'is_active'     => true,
            ]
        );

        // ─── (Tùy chọn) Thêm client khác nếu cần tích hợp thêm ─────────────
        // ApiClient::updateOrCreate(
        //     ['client_id' => 'another_system'],
        //     [
        //         'client_secret' => Hash::make('another_secret'),
        //         'name'          => 'Hệ thống khác',
        //         'is_active'     => true,
        //     ]
        // );

        $this->command->info('✅ Đã tạo API clients thành công.');
    }
}
