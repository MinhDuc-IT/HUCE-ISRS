<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherPaymentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Mã Giảng Viên',
            'Tên Giảng Viên',
            'Mã Môn Học',
            'Tên Môn Học',
            'Đợt Phụ Đạo',
            'Tổng Số Tiết',
            'Số Sinh Viên Đăng Ký',
            'Số Tiền Thanh Toán',
        ];
    }

    public function map($row): array
    {
        return [
            $row['teacher_code'],
            $row['teacher_name'],
            $row['course_code'],
            $row['course_name'],
            $row['term'],
            $row['total_periods'],
            $row['student_count'],
            $row['amount_formatted'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Căn lề và in đậm dòng đầu (headings)
            1 => ['font' => ['bold' => true]],
        ];
    }
}
