<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachingPaymentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $query;
    private $index = 0;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function map($row): array
    {
        $this->index++;
        $pricePerPeriod = (float) $row->price_per_period;
        $priceCoefficient = (float) $row->price_coefficient;
        $baseAmount = $pricePerPeriod * $priceCoefficient;
        $totalAmount = (int) $row->remedial_periods * $baseAmount;

        return [
            $this->index,
            $row->lecturer_name,
            $row->lecturer_phone,
            $row->subject_code,
            $row->subject_name,
            $row->remedial_periods,
            $pricePerPeriod,
            $baseAmount,
            $totalAmount,
        ];
    }

    public function headings(): array
    {
        return [
            'TT',
            'GV phụ đạo',
            'SĐT GV',
            'Mã MH',
            'Tên MH',
            'ST PĐ',
            'Đơn giá/ 1 tiết',
            'Số tiền (*hệ số 1)',
            'Tổng tiền',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
