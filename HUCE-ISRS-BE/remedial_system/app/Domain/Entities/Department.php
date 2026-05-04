<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Khoa / Bộ môn
 *
 * @property int    $id             ID khoa
 * @property string $departmentCode Mã khoa (VD: CNTT)
 * @property string $departmentName Tên khoa đầy đủ
 */
class Department
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $departmentCode,
        public readonly string  $departmentName,
        public readonly ?string $email = null,
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
