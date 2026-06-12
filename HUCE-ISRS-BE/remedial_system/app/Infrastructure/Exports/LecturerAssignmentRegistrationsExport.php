<?php

namespace App\Infrastructure\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LecturerAssignmentRegistrationsExport implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $registrations,
    ) {}

    public function array(): array
    {
        return $this->registrations->map(fn ($registration) => [
            $registration->user?->student_code ?? '-',
            $registration->user?->name ?? '-',
            $registration->remedialTerm?->name ?? '-',
            $registration->remedial_periods,
            $registration->registration_date?->format('d/m/Y H:i') ?? '-',
        ])->all();
    }

    public function headings(): array
    {
        return [
            'MSSV',
            'Họ và tên',
            'Đợt phụ đạo',
            'Số tiết',
            'Ngày đăng ký',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 28,
            'C' => 30,
            'D' => 12,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $highestRow = $this->registrations->count() + 1;
        $highestColumn = Coordinate::stringFromColumnIndex(5);

        return [
            AfterSheet::class => function (AfterSheet $event) use ($highestRow, $highestColumn): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$highestColumn}{$highestRow}");
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9E2F3'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(24);
            },
        ];
    }
}