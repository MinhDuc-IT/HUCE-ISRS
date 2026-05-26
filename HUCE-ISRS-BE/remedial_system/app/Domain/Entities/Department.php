<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Khoa / Bộ môn
 *
 * @property int    $id             ID khoa
 * @property string $departmentCode Mã bộ môn HUCE (IDBoMon)
 * @property string $departmentName Tên bộ môn
 * @property string|null $facultyCode IDPhongBan (ID khoa HUCE)
 * @property string|null $facultyName Tên khoa / đơn vị quản lý (TenPhongBan)
 */
class Department
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $departmentCode,
        public readonly string  $departmentName,
        public readonly ?string $facultyCode = null,
        public readonly ?string $facultyName = null,
        public readonly ?string $email = null,
        public readonly ?string $phoneNumber = null,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {}

    /**
     * Trả về chuỗi hiển thị đầy đủ: "CNTT – Công nghệ thông tin"
     */
    public function label(): string
    {
        return "{$this->departmentCode} – {$this->departmentName}";
    }
}
