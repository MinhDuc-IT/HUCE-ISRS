<?php

namespace App\DTOs;

use App\Models\KetQuaHocTap;
use stdClass;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CourseDto",
    description: "Thông tin môn học kèm kết quả của sinh viên"
)]
class CourseDto
{
    #[OA\Property(property: "courseCode", type: "string", example: "CS101-01", description: "Mã học phần")]
    public string $courseCode;

    #[OA\Property(property: "subjectCode", type: "string", example: "CS101", description: "Mã môn học")]
    public string $subjectCode;

    #[OA\Property(property: "subjectName", type: "string", example: "Lập trình hướng đối tượng", description: "Tên môn học")]
    public string $subjectName;

    #[OA\Property(property: "credits", type: "integer", example: 3, description: "Số tín chỉ")]
    public int $credits;

    #[OA\Property(property: "classSectionCode", type: "string", example: "CNTT01", description: "Mã lớp học phần")]
    public string $classSectionCode;

    #[OA\Property(property: "semesterOrder", type: "integer", example: 1, description: "Thứ tự học kỳ trong năm")]
    public int $semesterOrder;

    #[OA\Property(property: "academicYear", type: "integer", example: 2024, description: "Năm học")]
    public int $academicYear;

    #[OA\Property(property: "processScore", type: "number", format: "float", nullable: true, example: 8.5, description: "Điểm quá trình")]
    public ?float $processScore;

    #[OA\Property(property: "examScore", type: "number", format: "float", nullable: true, example: 5.0, description: "Điểm thi")]
    public ?float $examScore;

    #[OA\Property(property: "finalScore", type: "number", format: "float", nullable: true, example: 6.3, description: "Điểm tổng kết hệ 10")]
    public ?float $finalScore;

    #[OA\Property(property: "gpaScore", type: "number", format: "float", nullable: true, example: 2.0, description: "Điểm tổng kết hệ 4")]
    public ?float $gpaScore;

    #[OA\Property(property: "letterGrade", type: "string", nullable: true, example: "C+", description: "Điểm chữ")]
    public ?string $letterGrade;

    /**
     * Tạo DTO từ model KetQuaHocTap kèm các quan hệ LopHocPhan, MonHoc, Dot.
     *
     * @param KetQuaHocTap $ketQua Kết quả học tập của sinh viên
     */
    public static function fromKetQua(KetQuaHocTap $ketQua): self
    {
        $dto = new self();

        $lopHocPhan = $ketQua->lopHocPhan;
        $monHoc     = $lopHocPhan?->monHoc;
        $dot        = $lopHocPhan?->dot;

        $dto->courseCode       = $monHoc?->MaHocPhan ?? '';
        $dto->subjectCode      = $monHoc?->MaMonHoc ?? '';
        $dto->subjectName      = $monHoc?->TenMonHoc ?? '';
        $dto->credits          = (int) ($monHoc?->SoTinChi ?? 0);
        $dto->classSectionCode = $lopHocPhan?->MaLopHocPhan ?? '';
        $dto->semesterOrder    = (int) ($dot?->SoThuTu ?? 0);
        $dto->academicYear     = (int) ($dot?->IDNamHoc ?? 0);
        $dto->processScore     = $ketQua->DiemChuyenCan1;
        $dto->examScore        = $ketQua->DiemThi;
        $dto->finalScore       = $ketQua->DiemTongKet;
        $dto->gpaScore         = $ketQua->DiemTinChi;
        $dto->letterGrade      = $ketQua->DiemChu;

        return $dto;
    }

    /**
     * Tạo DTO từ kết quả raw của DB::table()->join()->get()
     *
     * Các cột dự kiến:
     *   MaHocPhan, MaMonHoc, TenMonHoc, SoTinChi,
     *   MaLopHocPhan, DiemChuyenCan1, DiemThi,
     *   DiemTongKet, DiemTinChi, DiemChu,
     *   HocKy (alias của dot.SoThuTu), IDNamHoc (alias từ nh.NamHoc khi join DM_NamHoc — năm học hiển thị)
     *
     * @param stdClass $row Một hàng kết quả từ DB::table
     */
    public static function fromRow(stdClass $row): self
    {
        $dto = new self();

        $dto->courseCode       = $row->MaHocPhan    ?? '';
        $dto->subjectCode      = $row->MaMonHoc     ?? '';
        $dto->subjectName      = $row->TenMonHoc    ?? '';
        $dto->credits          = (int) ($row->SoTinChi ?? 0);
        $dto->classSectionCode = $row->MaLopHocPhan ?? '';
        $dto->semesterOrder    = (int) ($row->HocKy   ?? 0);
        $dto->academicYear     = (int) ($row->IDNamHoc ?? 0);
        $dto->processScore     = isset($row->DiemChuyenCan1) ? (float) $row->DiemChuyenCan1 : null;
        $dto->examScore        = isset($row->DiemThi)        ? (float) $row->DiemThi        : null;
        $dto->finalScore       = isset($row->DiemTongKet)    ? (float) $row->DiemTongKet    : null;
        $dto->gpaScore         = isset($row->DiemTinChi)     ? (float) $row->DiemTinChi     : null;
        $dto->letterGrade      = $row->DiemChu ?? null;

        return $dto;
    }
}
