<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Tài khoản Admin ────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@remedial.edu.vn'],
            [
                'name'     => 'Quản trị viên',
                'password' => bcrypt('Admin@2024!'),
                'role'     => User::ROLE_ADMIN,
            ]
        );

        // ── Tài khoản Bộ môn (ví dụ) ───────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'bomon.cntt@remedial.edu.vn'],
            [
                'name'          => 'Bộ môn CNTT',
                'password'      => bcrypt('BoMon@2024!'),
                'role'          => User::ROLE_BO_MON,
                'department_id' => 1,
            ]
        );

        $this->command->info('✅ Seeded: Admin & Bộ môn accounts');
    }
}
