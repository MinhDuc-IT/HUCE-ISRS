<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $rows = require __DIR__ . '/data/huce_subjects.php';

        // Tra department_code -> id một lần để tránh query lặp.
        $deptIdByCode = Department::query()
            ->pluck('id', 'department_code');

        $created = 0;
        $skipped = 0;

        foreach ($rows as [$subjectCode, $name, $credits, $departmentCode]) {
            $departmentId = $deptIdByCode[(string) $departmentCode] ?? null;

            if ($departmentId === null) {
                $this->command?->warn(
                    "⚠️  Bỏ qua môn {$subjectCode}: không tìm thấy bộ môn có mã {$departmentCode}."
                );
                $skipped++;
                continue;
            }

            Subject::updateOrCreate(
                ['subject_code' => $subjectCode],
                [
                    'name'          => trim($name),
                    'credits'       => $credits,
                    'department_id' => $departmentId,
                    'is_deleted'    => false,
                ]
            );
            $created++;
        }

        $this->command?->info(
            "✅ SubjectSeeder: {$created} môn học" . ($skipped ? ", bỏ qua {$skipped}." : '.')
        );
    }
}
