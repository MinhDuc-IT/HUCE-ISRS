<?php

namespace App\DTOs;

use App\Models\SinhVien;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StudentDto",
    description: "Thông tin sinh viên"
)]
class StudentDto
{
    #[OA\Property(property: "id", type: "string", example: "SV001", description: "Mã sinh viên")]
    public string $id;

    #[OA\Property(property: "fullName", type: "string", example: "Nguyễn Văn An", description: "Họ và tên đầy đủ")]
    public string $fullName;

    #[OA\Property(property: "gender", type: "string", example: "Nam", description: "Giới tính")]
    public string $gender;

    #[OA\Property(property: "dateOfBirth", type: "string", example: "2002-05-15", description: "Ngày sinh")]
    public string $dateOfBirth;

    #[OA\Property(property: "placeOfBirth", type: "string", example: "Hà Nội", description: "Nơi sinh")]
    public string $placeOfBirth;

    #[OA\Property(property: "personalEmail", type: "string", example: "an@gmail.com", description: "Email cá nhân")]
    public string $personalEmail;

    #[OA\Property(property: "universityEmail", type: "string", nullable: true, example: "sv001@university.edu.vn", description: "Email trường")]
    public ?string $universityEmail;

    #[OA\Property(property: "gpaScale10", type: "number", format: "float", nullable: true, example: 7.5, description: "Điểm TB tích lũy hệ 10")]
    public ?float $gpaScale10;

    #[OA\Property(property: "gpaScale4", type: "number", format: "float", nullable: true, example: 2.8, description: "Điểm TB tích lũy hệ 4")]
    public ?float $gpaScale4;

    #[OA\Property(property: "gradeClassification", type: "string", nullable: true, example: "Khá", description: "Xếp loại học lực")]
    public ?string $gradeClassification;

    #[OA\Property(property: "totalCredits", type: "integer", nullable: true, example: 90, description: "Tổng tín chỉ tích lũy")]
    public ?int $totalCredits;

    #[OA\Property(property: "failedCredits", type: "integer", nullable: true, example: 3, description: "Số tín chỉ chưa đạt")]
    public ?int $failedCredits;

    /**
     * Tạo DTO từ Eloquent model SinhVien.
     *
     * @param SinhVien $model Model sinh viên kèm quan hệ
     */
    public static function fromModel(SinhVien $model): self
    {
        $dto = new self();
        $dto->id            = $model->MaSinhVien;
        $dto->fullName      = trim($model->HoDem . ' ' . $model->Ten);
        $dto->gender        = $model->GioiTinh;
        $dto->dateOfBirth   = $model->NgaySinh2;
        $dto->placeOfBirth  = $model->NoiSinh_Text ?? '';
        $dto->personalEmail = $model->Email ?? '';

        $dto->universityEmail = $model->emailNguoiDung?->EMail01;

        // Lấy tổng kết học kỳ mới nhất
        $latestTongKet = $model->tongKetDot?->sortByDesc('IDDot')->first();

        $dto->gpaScale10          = $latestTongKet?->DiemTBHocLucTichLuy;
        $dto->gpaScale4           = $latestTongKet?->DiemTBTinChiTichLuy;
        $dto->gradeClassification = $latestTongKet?->diemChuTichLuy;
        $dto->totalCredits        = $latestTongKet?->SoTCTichLuy;
        $dto->failedCredits       = $latestTongKet?->SoTCKhongDat;

        return $dto;
    }
}
