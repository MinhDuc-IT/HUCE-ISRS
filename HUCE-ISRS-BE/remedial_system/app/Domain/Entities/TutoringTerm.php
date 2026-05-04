<?php

namespace App\Domain\Entities;

use Carbon\Carbon;

/**
 * Domain Entity – Đợt phụ đạo (Aggregate Root)
 *
 * @property int         $id
 * @property int         $semesterId    Thuộc về học kỳ nào
 * @property string      $name          Tên đợt (VD: Đợt 1 HK1 2024-2025)
 * @property Carbon|null $startDate     Ngày bắt đầu đăng ký
 * @property Carbon|null $endDate       Ngày kết thúc đăng ký
 * @property int         $heSoPD        Hệ số đợt phụ đạo
 * @property int         $donGia1Tiet   Đơn giá một tiết
 * @property float       $heSoDonGia    Hệ số đơn giá
 * @property bool        $isDefault     Đợt mặc định hiện tại
 * @property Carbon      $createdAt
 */
class TutoringTerm
{
    public function __construct(
        public readonly ?int    $id,
        public readonly int     $semesterId,
        public readonly string  $name,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly int     $heSoPD = 1,
        public readonly int     $donGia1Tiet = 150000,
        public readonly float   $heSoDonGia = 1.0,
        public readonly bool    $isDefault = false,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {}

    /**
     * Kiểm tra đợt phụ đạo này có đang trong thời gian mở đăng ký hay không.
     */
    public function isRegistrationOpen(): bool
    {
        $now = Carbon::now();

        if ($this->startDate !== null && $now->lt($this->startDate)) {
            return false;
        }

        if ($this->endDate !== null && $now->gt($this->endDate)) {
            return false;
        }

        return true;
    }
}
