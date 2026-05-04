<?php

namespace App\Domain\Entities;

/**
 * Domain Entity – Thông tin sinh viên (đã qua Anti-Corruption Layer)
 *
 * Đây là representation nội bộ của sinh viên trong Remedial System.
 * KHÔNG phụ thuộc vào cấu trúc DB hay API của University System.
 *
 * @property string      $id                  Mã sinh viên
 * @property string      $fullName             Họ tên đầy đủ
 * @property string      $gender              Giới tính
 * @property string      $dateOfBirth         Ngày sinh
 * @property string      $placeOfBirth        Nơi sinh
 * @property string      $personalEmail       Email cá nhân
 * @property string|null $universityEmail     Email trường
 * @property float|null  $gpaScale10          Điểm TB tích lũy hệ 10
 * @property float|null  $gpaScale4           Điểm TB tích lũy hệ 4
 * @property string|null $gradeClassification Xếp loại học lực
 * @property int|null    $totalCredits        Tổng tín chỉ tích lũy
 * @property int|null    $failedCredits       Số tín chỉ chưa đạt
 */
class StudentInfo
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $fullName,
        public readonly string  $gender,
        public readonly string  $dateOfBirth,
        public readonly string  $placeOfBirth,
        public readonly string  $personalEmail,
        public readonly ?string $universityEmail,
        public readonly ?float  $gpaScale10,
        public readonly ?float  $gpaScale4,
        public readonly ?string $gradeClassification,
        public readonly ?int    $totalCredits,
        public readonly ?int    $failedCredits,
    ) {}

    /**
     * Kiểm tra sinh viên có đang nợ môn (có tín chỉ chưa đạt).
     */
    public function hasFailedCredits(): bool
    {
        return ($this->failedCredits ?? 0) > 0;
    }

    /**
     * Kiểm tra sinh viên có đủ điều kiện đăng ký học bổ sung (GPA > 0).
     */
    public function isEligibleForRemedial(): bool
    {
        return $this->hasFailedCredits();
    }
}
