<?php

namespace App\Domain\Enums;

enum TutoringClassStatus: int
{
    case OPEN      = 1;
    case FULL      = 2;
    case CLOSED    = 3;
    case CANCELLED = 0;

    public function label(): string
    {
        return match($this) {
            self::OPEN      => 'Đang mở đăng ký',
            self::FULL      => 'Đã đủ sĩ số',
            self::CLOSED    => 'Đã đóng',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
