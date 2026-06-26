<?php

namespace App\DTOs;

use stdClass;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisteredCourseDto',
    description: 'Môn học sinh viên đã đăng ký học chính quy theo kỳ/năm học'
)]
class RegisteredCourseDto
{
    #[OA\Property(property: 'subjectCode', type: 'string', example: 'CS101')]
    public string $subjectCode;

    #[OA\Property(property: 'subjectName', type: 'string', example: 'Lập trình hướng đối tượng')]
    public string $subjectName;

    #[OA\Property(property: 'credits', type: 'integer', example: 3)]
    public int $credits;

    #[OA\Property(property: 'classSectionCode', type: 'string', example: 'CS101-01')]
    public string $classSectionCode;

    #[OA\Property(property: 'plannedClass', type: 'string', example: 'CNTT01', description: 'Lớp dự kiến (LopDuKien)')]
    public string $plannedClass;

    #[OA\Property(property: 'registrationDate', type: 'string', nullable: true, example: '2024-09-01')]
    public ?string $registrationDate;

    #[OA\Property(property: 'registrationId', type: 'integer', example: 12345)]
    public int $registrationId;

    #[OA\Property(property: 'registrationStatusId', type: 'integer', example: 2)]
    public int $registrationStatusId;

    #[OA\Property(property: 'registrationStatusName', type: 'string', example: 'Đã duyệt')]
    public string $registrationStatusName;

    #[OA\Property(property: 'academicYearLabel', type: 'string', example: '2024-2025')]
    public string $academicYearLabel;

    #[OA\Property(property: 'academicYear', type: 'integer', example: 2024)]
    public int $academicYear;

    #[OA\Property(property: 'semesterOrder', type: 'integer', example: 1)]
    public int $semesterOrder;

    #[OA\Property(property: 'termName', type: 'string', example: 'Học kỳ 1')]
    public string $termName;

    #[OA\Property(property: 'examDate', type: 'string', nullable: true, example: '2025-01-15', description: 'Ngày thi (ưu tiên TKB_LichThi, fallback TKB_DanhSachLopHocPhanThi)')]
    public ?string $examDate;

    public static function fromRow(stdClass $row): self
    {
        $dto = new self();

        $dto->subjectCode            = (string) ($row->MaMonHoc ?? '');
        $dto->subjectName            = (string) ($row->TenMonHoc ?? '');
        $dto->credits                = (int) ($row->SoTinChi ?? 0);
        $dto->classSectionCode       = (string) ($row->MaLopHocPhan ?? '');
        $dto->plannedClass           = (string) ($row->LopDuKien ?? '');
        $dto->registrationDate       = isset($row->NgayDangKy) ? (string) $row->NgayDangKy : null;
        $dto->registrationId         = (int) ($row->IDDangKyHocPhan ?? 0);
        $dto->registrationStatusId   = (int) ($row->IDTrangThaiDangKy ?? 0);
        $dto->registrationStatusName = (string) ($row->TrangThaiDangKy ?? '');
        $dto->academicYearLabel      = (string) ($row->NienHoc ?? '');
        $dto->academicYear           = (int) ($row->NamHoc ?? 0);
        $dto->semesterOrder          = (int) ($row->HocKy ?? 0);
        $dto->termName               = (string) ($row->TenDot ?? '');
        $dto->examDate               = isset($row->NgayThi) ? (string) $row->NgayThi : null;

        return $dto;
    }
}
