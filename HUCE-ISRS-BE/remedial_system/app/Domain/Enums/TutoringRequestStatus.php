<?php

namespace App\Domain\Enums;

enum TutoringRequestStatus: int
{
    case PENDING   = 1;
    case APPROVED  = 2;
    case REJECTED  = 0;
    case CANCELLED = 3;
    case PAID      = 4;

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Chờ duyệt',
            self::APPROVED  => 'Đã duyệt',
            self::REJECTED  => 'Từ chối',
            self::CANCELLED => 'Đã hủy',
            self::PAID      => 'Đã thanh toán',
        };
    }
}
