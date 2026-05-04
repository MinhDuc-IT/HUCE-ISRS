<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder cho University System.
 *
 * CHỈ tạo dữ liệu cho bảng api_clients (bảng mới, không có trong DB gốc).
 * Không tạo dữ liệu sinh viên/môn học vì đã có sẵn trong DB trường được restore từ .bak.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ApiClientSeeder::class);
    }
}
