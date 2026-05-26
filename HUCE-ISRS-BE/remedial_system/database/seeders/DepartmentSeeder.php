<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = require __DIR__ . '/data/huce_departments.php';

        foreach ($rows as [$idBoMon, $tenBoMon, $idPhongBan, $tenPhongBan]) {
            Department::updateOrCreate(
                ['department_code' => (string) $idBoMon],
                [
                    'name'         => trim($tenBoMon),
                    'faculty_code' => (string) $idPhongBan,
                    'faculty_name' => trim($tenPhongBan),
                    'email'        => null,
                    'phone_number' => null,
                    'is_deleted'   => false,
                ]
            );
        }

        $this->command?->info('✅ DepartmentSeeder: ' . count($rows) . ' bộ môn HUCE.');
    }
}
