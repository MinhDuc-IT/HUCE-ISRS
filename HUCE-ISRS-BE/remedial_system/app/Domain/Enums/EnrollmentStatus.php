<?php

namespace App\Domain\Enums;

enum EnrollmentStatus: int
{
    case ENROLLED  = 1;
    case ACTIVE    = 2;
    case CANCELLED = 0;

    public function label(): string
    {
        return match($this) {
            self::ENROLLED  => 'Đã đăng ký',
            self::ACTIVE    => 'Đang học',
            self::CANCELLED => 'Đã hủy',
        };
    }
}
