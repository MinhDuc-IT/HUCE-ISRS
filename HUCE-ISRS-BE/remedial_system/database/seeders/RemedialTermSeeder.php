<?php

namespace Database\Seeders;

use App\Models\RemedialTerm;
use Illuminate\Database\Seeder;

class RemedialTermSeeder extends Seeder
{
    public function run(): void
    {
        $rows = require __DIR__ . '/data/huce_remedial_terms.php';

        foreach ($rows as [
            $name, $year, $semester,
            $startDate, $endDate, $regStart, $regEnd,
            $status, $isCurrent,
        ]) {
            RemedialTerm::updateOrCreate(
                ['year' => $year, 'semester' => $semester],
                [
                    'name'                 => $name,
                    'start_date'           => $startDate . ' 00:00:00',
                    'end_date'             => $endDate . ' 23:59:59',
                    'registration_start'   => $regStart . ' 00:00:00',
                    'registration_end'     => $regEnd . ' 23:59:59',
                    'remedial_coefficient' => 1,
                    'price_per_period'     => 150000,
                    'price_coefficient'    => 1,
                    'status'               => $status,
                    'is_current_term'      => $isCurrent,
                    'is_deleted'           => false,
                ]
            );
        }

        $this->command?->info('✅ RemedialTermSeeder: ' . count($rows) . ' đợt phụ đạo.');
    }
}
